<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KaryawanModel;
use App\Models\AbsensiModel;
use App\Models\PenggajianModel;
use App\Models\TransaksiKasModel;

class HRD extends BaseController
{
    protected $karyawanModel;
    protected $absensiModel;
    protected $penggajianModel;
    protected $kasModel;

    public function __construct()
    {
        $this->karyawanModel   = new KaryawanModel();
        $this->absensiModel    = new AbsensiModel();
        $this->penggajianModel = new PenggajianModel();
        $this->kasModel        = new TransaksiKasModel();
    }

    // ==========================================
    // KARYAWAN
    // ==========================================
    public function karyawan()
    {
        $data['karyawan'] = $this->karyawanModel->findAll();
        return view('hrd/karyawan', $data);
    }

    public function storeKaryawan()
    {
        $id = $this->request->getPost('id_karyawan');
        $data = [
            'nama_karyawan'     => $this->request->getPost('nama_karyawan'),
            'posisi'            => $this->request->getPost('posisi'),
            'rate_gaji_per_jam' => $this->request->getPost('rate_gaji_per_jam') ?? 10000
        ];

        if ($id) {
            $this->karyawanModel->update($id, $data);
            $msg = 'Data Karyawan berhasil diperbarui.';
        } else {
            $this->karyawanModel->save($data);
            $msg = 'Data Karyawan berhasil ditambahkan.';
        }

        return redirect()->to('/hrd/karyawan')->with('success', $msg);
    }

    public function deleteKaryawan($id)
    {
        $this->karyawanModel->delete($id);
        return redirect()->to('/hrd/karyawan')->with('success', 'Data Karyawan berhasil dihapus.');
    }

    // ==========================================
    // ABSENSI
    // ==========================================
    public function absensi()
    {
        $data['absensi']  = $this->absensiModel->getAbsensiWithKaryawan();
        $data['karyawan'] = $this->karyawanModel->findAll();
        return view('hrd/absensi', $data);
    }

    public function storeAbsensi()
    {
        $id        = $this->request->getPost('id_absensi');
        $idKaryawan= $this->request->getPost('id_karyawan');
        $tanggal   = $this->request->getPost('tanggal');
        $jamMasuk  = $this->request->getPost('jam_masuk');
        $jamKeluar = $this->request->getPost('jam_keluar');
        
        // 0. Cek Periode Penggajian (Terkunci jika LUNAS)
        $periodeBulan = substr($tanggal, 0, 7);
        $penggajian = $this->penggajianModel->where('id_karyawan', $idKaryawan)
                                            ->where('periode_bulan', $periodeBulan)
                                            ->first();
        if ($penggajian && $penggajian['status_pembayaran'] === 'Sudah Dibayar') {
            return redirect()->back()->withInput()->with('error', 'Gaji bulan ini sudah LUNAS. Data absensi dikunci dan tidak dapat diubah.');
        }

        // 1. Cek duplikasi jika ini adalah input baru (bukan edit)
        if (!$id) {
            $existing = $this->absensiModel->where('id_karyawan', $idKaryawan)
                                          ->where('tanggal', $tanggal)
                                          ->first();
            if ($existing) {
                return redirect()->back()->withInput()->with('error', 'Karyawan ini sudah memiliki catatan absensi pada tanggal tersebut.');
            }
        }

        // 2. Kalkulasi Total Jam Kerja
        $totalJam = 0;
        if ($jamMasuk && $jamKeluar) {
            $t1 = strtotime($jamMasuk);
            $t2 = strtotime($jamKeluar);
            if ($t2 > $t1) {
                $totalJam = round(($t2 - $t1) / 3600, 2);
            }
        }

        $data = [
            'id_karyawan'     => $idKaryawan,
            'tanggal'         => $tanggal,
            'jam_masuk'       => $jamMasuk,
            'jam_keluar'      => $jamKeluar,
            'total_jam_kerja' => $totalJam,
        ];

        if ($id) {
            $this->absensiModel->update($id, $data);
            $msg = 'Data Absensi berhasil diperbarui. Penggajian akan disinkronisasi.';
        } else {
            $this->absensiModel->save($data);
            $msg = 'Data Absensi berhasil ditambahkan. Penggajian otomatis disinkronisasi.';
        }

        // 3. Realtime Integrasi Data: Sync ke Tabel Penggajian
        $this->syncPenggajian($idKaryawan, $tanggal);

        return redirect()->to('/hrd/absensi')->with('success', $msg);
    }

    public function deleteAbsensi($id)
    {
        $absensi = $this->absensiModel->find($id);
        if (!$absensi) return redirect()->to('/hrd/absensi')->with('error', 'Data tidak ditemukan.');
        
        // Proteksi Hapus jika Gaji Lunas
        $periodeBulan = substr($absensi['tanggal'], 0, 7);
        $penggajian = $this->penggajianModel->where('id_karyawan', $absensi['id_karyawan'])
                                            ->where('periode_bulan', $periodeBulan)
                                            ->first();
        if ($penggajian && $penggajian['status_pembayaran'] === 'Sudah Dibayar') {
            return redirect()->to('/hrd/absensi')->with('error', 'Gaji untuk absensi ini sudah LUNAS. Data tidak dapat dihapus.');
        }

        // Realtime Integrasi: Tahan ID dan Tanggal sebelum dihapus 
        $idKar = $absensi['id_karyawan'];
        $tgl   = $absensi['tanggal'];

        // Hapus Absensi
        $this->absensiModel->delete($id);
        
        // Sync Ulang Gaji (mungkin berkurang jadi 0)
        $this->syncPenggajian($idKar, $tgl);

        return redirect()->to('/hrd/absensi')->with('success', 'Data Absensi berhasil dihapus. Gaji dikalkulasi ulang secara realtime.');
    }

    /**
     * Engine Realtime Sync Penggajian (God-Tier Integration)
     */
    private function syncPenggajian($idKaryawan, $tanggal)
    {
        $periodeBulan = substr($tanggal, 0, 7); // Format: YYYY-MM
        
        // Cek apakah data draft penggajian eksis di bulan tsb
        $penggajian = $this->penggajianModel->where('id_karyawan', $idKaryawan)
                                            ->where('periode_bulan', $periodeBulan)
                                            ->first();
        
        if ($penggajian && $penggajian['status_pembayaran'] === 'Belum Dibayar') {
            // Kalkulasi ulang total absensi
            $karyawan = $this->karyawanModel->find($idKaryawan);
            $db = \Config\Database::connect();
            $query = $db->query("
                SELECT SUM(total_jam_kerja) as total_jam
                FROM absensi
                WHERE id_karyawan = ? AND DATE_FORMAT(tanggal, '%Y-%m') = ?
            ", [$idKaryawan, $periodeBulan]);
            
            $totalJam = $query->getRow()->total_jam ?? 0;
            
            if ($totalJam <= 0) {
                // Jam habis (semua absen dihapus), tarik form gaji
                $this->penggajianModel->delete($penggajian['id_penggajian']);
            } else {
                // Auto Adjust Realtime nominal dan jam
                $totalGaji = $totalJam * $karyawan['rate_gaji_per_jam'];
                $this->penggajianModel->update($penggajian['id_penggajian'], [
                    'total_jam'  => $totalJam,
                    'total_gaji' => $totalGaji
                ]);
            }
        }
    }

    // ==========================================
    // PENGGAJIAN
    // ==========================================
    public function penggajian()
    {
        $data['penggajian'] = $this->penggajianModel->getPenggajianWithKaryawan();
        $data['karyawan']   = $this->karyawanModel->findAll();
        return view('hrd/penggajian', $data);
    }

    public function generateGaji()
    {
        $idKaryawan   = $this->request->getPost('id_karyawan');
        $periodeBulan = $this->request->getPost('periode_bulan'); // e.g. "2026-04"
        
        // 0. Cek Duplikasi (Enterprise Logic Lock)
        // Tidak boleh generate 2 kali slip gaji untuk Karyawan & Periode yang sama
        $existing = $this->penggajianModel->where('id_karyawan', $idKaryawan)
                                          ->where('periode_bulan', $periodeBulan)
                                          ->first();
        if ($existing) {
            $statusStr = $existing['status_pembayaran'] === 'Sudah Dibayar' ? 'sudah LUNAS' : 'sedang MENUNGGU PEMBAYARAN';
            return redirect()->back()->with('error', "Slip gaji untuk periode $periodeBulan sudah digenerate sebelumnya dan saat ini $statusStr. Anda tidak bisa membuat tagihan ganda.");
        }

        // 1. Dapatkan detail karyawan untuk rate
        $karyawan = $this->karyawanModel->find($idKaryawan);
        if (!$karyawan) return redirect()->back()->with('error', 'Karyawan tidak ditemukan.');

        // 2. Kalkulasi total jam kerja bulan terkait
        $db = \Config\Database::connect();
        $query = $db->query("
            SELECT SUM(total_jam_kerja) as total_jam
            FROM absensi
            WHERE id_karyawan = ? AND DATE_FORMAT(tanggal, '%Y-%m') = ?
        ", [$idKaryawan, $periodeBulan]);
        
        $totalJam = $query->getRow()->total_jam ?? 0;
        
        if ($totalJam <= 0) {
            return redirect()->back()->with('error', 'Karyawan belum memiliki jam kerja pada periode ini.');
        }

        $totalGaji = $totalJam * $karyawan['rate_gaji_per_jam'];

        // 3. Simpan ke draft penggajian
        $this->penggajianModel->save([
            'id_karyawan'       => $idKaryawan,
            'periode_bulan'     => $periodeBulan,
            'total_jam'         => $totalJam,
            'total_gaji'        => $totalGaji,
            'status_pembayaran' => 'Belum Dibayar'
        ]);

        return redirect()->to('/hrd/penggajian')->with('success', "Gaji Periode $periodeBulan berhasil di-generate otomatis.");
    }

    public function bayarGaji($id)
    {
        $gaji = $this->penggajianModel->find($id);
        if ($gaji && $gaji['status_pembayaran'] === 'Belum Dibayar') {
            
            // 1. Update status
            $this->penggajianModel->update($id, [
                'status_pembayaran' => 'Sudah Dibayar',
                'tanggal_bayar'     => date('Y-m-d H:i:s')
            ]);
            
            $karyawan = $this->karyawanModel->find($gaji['id_karyawan']);

            // 2. Automasi: Tarik Kas untuk bayar gaji (menghindari duplikasi input manual di modul Finance kita)
            $this->kasModel->save([
                'tanggal'        => date('Y-m-d'),
                'tipe_transaksi' => 'Pengeluaran',
                'kategori'       => 'Biaya Gaji / Upah Karyawan',
                'keterangan'     => "Pembayaran Gaji an. " . $karyawan['nama_karyawan'] . " Periode " . $gaji['periode_bulan'],
                'nominal'        => $gaji['total_gaji']
            ]);

            return redirect()->to('/hrd/penggajian')->with('success', 'Gaji berhasil dibayarkan dan Kas berkurang otomatis.');
        }
        return redirect()->back()->with('error', 'Data tidak valid atau sudah dibayar.');
    }
}
