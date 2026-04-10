<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;
use App\Models\TransaksiPesananModel;
use App\Models\DetailPesananModel;
use App\Models\ProductMappingModel;

/**
 * ExcelImporter — Library untuk import TikTok OrderSKUList .xlsx ke database
 *
 * Business Rules yang diimplementasikan:
 * 1. Upsert by Order ID (insert baru / update tanpa reset status_penarikan)
 * 2. Detail pesanan di-rebuild ulang setiap re-import
 * 3. kombinasi_produk: variasi "Default" → lookup product_mapping
 * 4. Hasil hanya relevan untuk status "Selesai" (difilter di Views/laporan)
 */
class ExcelImporter
{
    // Nama kolom TikTok OrderSKUList (verbatim dari header Excel)
    private const REQUIRED_COLS = [
        'Order ID',
        'Order Status',
        'Product Name',
        'Variation',
        'Quantity',
    ];

    private const OPTIONAL_COLS = [
        'SKU Order Amount',
        'SKU Settlement Amount',
        'Create Time',
        'Paid Time',
    ];

    private TransaksiPesananModel $transaksiModel;
    private DetailPesananModel    $detailModel;
    private ProductMappingModel   $mappingModel;
    private \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->transaksiModel = new TransaksiPesananModel();
        $this->detailModel    = new DetailPesananModel();
        $this->mappingModel   = new ProductMappingModel();
        $this->db             = \Config\Database::connect();
    }

    // ===========================================================
    // MAIN: Proses file Excel dan kembalikan summary
    // ===========================================================
    public function import(string $filePath, string $filename = ''): array
    {
        $result = [
            'success'      => false,
            'filename'     => $filename ?: basename($filePath),
            'total_rows'   => 0,
            'total_orders' => 0,
            'inserted'     => 0,
            'updated'      => 0,
            'skipped'      => 0,
            'errors'       => [],
            'preview'      => [],
        ];

        // 1. Load Excel
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet       = $spreadsheet->getActiveSheet();
            $rawRows     = $sheet->toArray(null, true, true, false);
        } catch (\Exception $e) {
            $result['errors'][] = 'Gagal membaca file Excel: ' . $e->getMessage();
            return $result;
        }

        // 2. Auto-detect header row
        $headerIdx = $this->detectHeaderRow($rawRows);
        if ($headerIdx === null) {
            $result['errors'][] = 'Kolom "Order ID" tidak ditemukan. Pastikan file adalah TikTok OrderSKUList export.';
            return $result;
        }

        // 3. Build column index map
        $colMap = $this->buildColMap($rawRows[$headerIdx]);

        // 4. Validasi kolom wajib
        foreach (self::REQUIRED_COLS as $col) {
            if (!isset($colMap[$col])) {
                $result['errors'][] = "Kolom wajib tidak ditemukan: \"$col\"";
                return $result;
            }
        }

        // 5. Load product mapping ke cache
        $this->mappingModel->loadCache();

        // 6. Baca dan grouping data per Order ID
        $groups = $this->groupRows($rawRows, $headerIdx, $colMap, $result);

        $result['total_orders'] = count($groups);

        // 7. Upsert ke database dalam satu transaction
        $this->db->transBegin();
        try {
            $previewCount = 0;

            foreach ($groups as $orderId => $data) {
                $wasExisting = $this->transaksiModel->exists($orderId);

                // Upsert header (status_penarikan TIDAK diubah)
                $this->transaksiModel->upsert([
                    'order_id'       => $orderId,
                    'platform'       => 'TikTok',
                    'status_pesanan' => $data['status_pesanan'],
                    'total_amount'   => round($data['total_amount'], 2),
                    'create_time'    => $data['create_time'],
                    'paid_time'      => $data['paid_time'],
                ]);

                // Rebuild detail: delete lama → insert baru
                $this->detailModel->deleteByOrderId($orderId);
                $this->detailModel->bulkInsert($orderId, $data['items']);

                $wasExisting ? $result['updated']++ : $result['inserted']++;

                // Preview (10 pertama)
                if ($previewCount < 10) {
                    $result['preview'][] = [
                        'order_id'      => $orderId,
                        'status_pesanan'=> $data['status_pesanan'],
                        'total_amount'  => round($data['total_amount'], 2),
                        'items'         => count($data['items']),
                        'action'        => $wasExisting ? 'UPDATED' : 'INSERTED',
                        'produk_sample' => $data['items'][0]['kombinasi_produk'] ?? '-',
                    ];
                    $previewCount++;
                }
            }

            $this->db->transCommit();
            $result['success'] = true;

            // Log import session
            $this->logImport($result);

        } catch (\Throwable $e) {
            $this->db->transRollback();
            $result['errors'][] = 'Database error: ' . $e->getMessage();
            $result['success']  = false;
        }

        return $result;
    }

    // ===========================================================
    // PRIVATE HELPERS
    // ===========================================================

    private function detectHeaderRow(array $rows): ?int
    {
        foreach ($rows as $idx => $row) {
            foreach ($row as $cell) {
                if (trim((string)$cell) === 'Order ID') {
                    return $idx;
                }
            }
        }
        return null;
    }

    private function buildColMap(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $idx => $cell) {
            $map[trim((string)$cell)] = $idx;
        }
        return $map;
    }

    private function groupRows(array $rawRows, int $headerIdx, array $colMap, array &$result): array
    {
        $groups = [];

        // Fitur $get robust: case-insensitive, mendukung array alias (Created Time / Create Time dll)
        $get = function (array $row, $colCandidates, array $colMap, $default = '') {
            $candidates = (array)$colCandidates;
            foreach ($candidates as $col) {
                $k = strtolower(trim($col));
                // Exact match (case insensitive)
                if (isset($colMap[$k])) {
                    $val = $row[$colMap[$k]];
                    if ($val !== null && $val !== '') return $val;
                }
                // Partial match fallback
                foreach ($colMap as $actualCol => $idx) {
                    if (strpos($actualCol, $k) !== false || strpos($k, $actualCol) !== false) {
                        $val = $row[$idx];
                        if ($val !== null && $val !== '') return $val;
                    }
                }
            }
            return $default;
        };

        // Helper untuk parse uang (Rp 15.000,00 -> 15000)
        $parseCurrency = function($val) {
            if (is_numeric($val)) return (float)$val;
            $val = str_ireplace(['rp', 'idr', ' ', 'Rp.', 'Rp', "\xC2\xA0"], '', (string)$val); // bersihkan
            if (strpos($val, ',') !== false && strpos($val, '.') !== false) {
                if (strrpos($val, ',') > strrpos($val, '.')) {
                    $val = str_replace('.', '', $val); $val = str_replace(',', '.', $val);
                } else {
                    $val = str_replace(',', '', $val);
                }
            } else if (strpos($val, ',') !== false) {
                if (preg_match('/,\d{1,2}$/', $val)) $val = str_replace(',', '.', $val);
                else $val = str_replace(',', '', $val);
            } else if (strpos($val, '.') !== false) {
                if (!preg_match('/\.\d{1,2}$/', $val)) $val = str_replace('.', '', $val);
            }
            return (float)$val;
        };

        for ($i = $headerIdx + 1; $i < count($rawRows); $i++) {
            $row     = $rawRows[$i];
            $orderId = trim((string)$get($row, ['Order ID', 'Order_ID', 'ID Pesanan'], $colMap));

            if ($orderId === '') continue;

            $result['total_rows']++;

            $statusPesanan = trim((string)$get($row, ['Order Status', 'Status Pesanan'], $colMap));
            $namaProduk    = trim((string)$get($row, ['Product Name', 'Nama Produk'], $colMap));
            $variasi       = trim((string)$get($row, ['Variation', 'Variasi'], $colMap));
            
            $qtyRaw        = $get($row, ['Quantity', 'Kuantitas', 'Qty'], $colMap, 0);
            $qty           = (int)preg_replace('/[^\d]/', '', (string)$qtyRaw);

            $skuOrderAmt   = $parseCurrency($get($row, ['SKU Order Amount', 'Order Amount', 'Total Harga SKU'], $colMap, 0));
            $skuSettleAmt  = $parseCurrency($get($row, ['SKU Settlement Amount', 'Settlement Amount', 'Penyelesaian', 'Total Diterima'], $colMap, 0));
            
            $createTime    = $this->parseDate($get($row, ['Created Time', 'Create Time', 'Waktu Dibuat', 'Order Creation'], $colMap));
            $paidTime      = $this->parseDate($get($row, ['Paid Time', 'Payment Time', 'Waktu Pembayaran'], $colMap));

            if (!isset($groups[$orderId])) {
                $groups[$orderId] = [
                    'status_pesanan' => $statusPesanan,
                    'total_amount'   => 0.0,
                    'create_time'    => $createTime,
                    'paid_time'      => $paidTime,
                    'items'          => [],
                ];
            }

            $groups[$orderId]['total_amount'] += $skuSettleAmt;

            // Kombinasi produk
            $kombinasi = $this->buildKombinasi($namaProduk, $variasi);

            $groups[$orderId]['items'][] = [
                'nama_produk_raw'    => $namaProduk,
                'variasi_raw'        => $variasi,
                'kombinasi_produk'   => $kombinasi,
                'quantity'           => $qty,
                'sku_order_amount'   => $skuOrderAmt,
                'sku_settlement_amt' => $skuSettleAmt,
            ];
        }

        return $groups;
    }

    /**
     * Generate kombinasi_produk:
     * - Jika variasi == "Default" atau kosong → cek product_mapping, atau pakai nama produk saja
     * - Jika ada variasi → "{nama} — {variasi}"
     */
    private function buildKombinasi(string $nama, string $variasi): string
    {
        $cleanVariasi = trim($variasi);

        // Cek mapping dulu (berlaku untuk semua variasi, termasuk Default)
        $mapped = $this->mappingModel->getKombinasi($nama, $cleanVariasi);
        if ($mapped !== null) {
            return $mapped;
        }

        // Variasi "Default" atau kosong → pakai nama produk saja
        if ($cleanVariasi === '' || strtolower($cleanVariasi) === 'default') {
            return trim($nama);
        }

        // Variasi spesifik → gabungkan
        return trim($nama) . ' — ' . $cleanVariasi;
    }

    /** Parse tanggal Excel (serial number atau string) */
    private function parseDate($val): ?string
    {
        if (empty($val)) return null;
        if (is_numeric($val)) {
            try {
                $dt = SpreadsheetDate::excelToDateTimeObject((float)$val);
                return $dt->format('Y-m-d H:i:s');
            } catch (\Exception $e) {}
        }
        $ts = strtotime((string)$val);
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
    }

    private function logImport(array $result): void
    {
        try {
            $this->db->query(
                "INSERT INTO import_log
                    (filename, platform, total_rows, total_orders, inserted, updated, skipped)
                 VALUES (?, 'TikTok', ?, ?, ?, ?, ?)",
                [
                    $result['filename'],
                    $result['total_rows'],
                    $result['total_orders'],
                    $result['inserted'],
                    $result['updated'],
                    $result['skipped'],
                ]
            );
        } catch (\Throwable $e) {
            // Log gagal tidak harus hentikan import
        }
    }
}
