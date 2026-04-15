<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TransaksiKasModel;
use App\Models\PenggajianModel;
use App\Models\KaryawanModel;

class Finance extends BaseController
{
    protected $kasModel;
    protected $penggajianModel;
    protected $karyawanModel;

    public function __construct()
    {
        $this->kasModel = new TransaksiKasModel();
        $this->penggajianModel = new PenggajianModel();
        $this->karyawanModel = new KaryawanModel();
    }

    public function index()
    {
        // Ambil data gaji yang belum dibayar beserta nama karyawan untuk form Dinamis
        $unpaidGaji = $this->penggajianModel
            ->select('penggajian.*, karyawan.nama_karyawan')
            ->join('karyawan', 'karyawan.id_karyawan = penggajian.id_karyawan')
            ->where('status_pembayaran', 'Belum Dibayar')
            ->orderBy('periode_bulan', 'ASC')
            ->findAll();

        $data = [
            'transaksi'  => $this->kasModel->orderBy('tanggal', 'DESC')->findAll(),
            'saldo'      => $this->kasModel->getSaldoAkhir(),
            'unpaidGaji' => $unpaidGaji
        ];
        return view('finance/pengeluaran', $data);
    }

    public function store()
    {
        $tanggal        = $this->request->getPost('tanggal');
        $tipe_transaksi = $this->request->getPost('tipe_transaksi');
        $kategori       = $this->request->getPost('kategori');
        $keterangan     = $this->request->getPost('keterangan');
        $nominal        = $this->request->getPost('nominal');
        $id_penggajian  = $this->request->getPost('id_penggajian');

        // Validasi input dasar
        if (!$this->validate([
            'tanggal'        => 'required|valid_date',
            'tipe_transaksi' => 'required|in_list[Pemasukan,Pengeluaran]',
            'kategori'       => 'required'
        ])) {
            return redirect()->back()->withInput()->with('error', 'Cek kembali tipe dan kategori yang Anda masukkan.');
        }

        // Skenario Pemasukan (Security Block)
        if ($tipe_transaksi === 'Pemasukan') {
            if ($kategori === 'Biaya Gaji / Upah Karyawan') {
                return redirect()->back()->withInput()->with('error', 'FATAL: Kategori Gaji tidak bisa dicatat sebagai Pemasukan.');
            }
        }

        // The "God-Tier" Transaction Locking untuk Skenario Pengeluaran > Gaji
        if ($tipe_transaksi === 'Pengeluaran' && $kategori === 'Biaya Gaji / Upah Karyawan') {
            if (empty($id_penggajian)) {
                return redirect()->back()->withInput()->with('error', 'Pilih slip gaji yang ingin dibayarkan pada dropdown.');
            }

            // Lock 1: Verifikasi Gaji
            $gajiTarget = $this->penggajianModel->find($id_penggajian);
            if (!$gajiTarget) {
                return redirect()->back()->withInput()->with('error', 'Data tagihan gaji tidak ditemukan di sistem HRD.');
            }
            
            // Lock 2: Mencegah Race Condition (Double Payment)
            if ($gajiTarget['status_pembayaran'] === 'Sudah Dibayar') {
                return redirect()->back()->with('error', 'GAGAL DIBAYAR: Tagihan slip gaji ini baru saja selesai dibayar sebelumnya! Saldo kas diselamatkan dari double-payment.');
            }

            // Lock 3: Override Nominal murni dari database HRD (Anti manipulasi inspect element form)
            $karyawan = $this->karyawanModel->find($gajiTarget['id_karyawan']);
            $nominalKas = $gajiTarget['total_gaji'];
            $keteranganKas = "Pembayaran Gaji an. " . $karyawan['nama_karyawan'] . " Periode " . $gajiTarget['periode_bulan'];

            // Eksekusi Ledger Kas
            $this->kasModel->save([
                'tanggal'        => $tanggal,
                'tipe_transaksi' => 'Pengeluaran',
                'kategori'       => 'Biaya Gaji / Upah Karyawan',
                'keterangan'     => $keteranganKas,
                'nominal'        => $nominalKas
            ]);

            // Eksekusi Tutup Slip Gaji HRD secara instan
            $this->penggajianModel->update($id_penggajian, ['status_pembayaran' => 'Sudah Dibayar']);

            return redirect()->to('/finance/pengeluaran')->with('success', 'Transaksi berhasil! Gaji dilunasi & Kas operasional Perusahaan terpotong ' . number_format($nominalKas, 2, ',', '.'));
        }

        // Regular Transaksi Workflow (Input Manual Listrik, Pencairan, Penjualan, dll)
        if (empty($nominal) || !is_numeric($nominal) || $nominal <= 0) {
            return redirect()->back()->withInput()->with('error', 'Masukan nominal kas yang benar.');
        }

        $this->kasModel->save([
            'tanggal'        => $tanggal,
            'tipe_transaksi' => $tipe_transaksi,
            'kategori'       => $kategori,
            'keterangan'     => $keterangan,
            'nominal'        => $nominal,
        ]);

        return redirect()->to('/finance/pengeluaran')->with('success', 'Transaksi Kas (' . $tipe_transaksi . ') berhasil ditambahkan!');
    }
}
