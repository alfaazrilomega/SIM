<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKasAndHRModule extends Migration
{
    public function up(): void
    {
        // --------------------------------------------------------------------
        // 1. TABEL TRANSAKSI KAS (Shared View / Laporan)
        // --------------------------------------------------------------------
        // Didesain untuk digabungkan di satu View/Laporan.
        // Rekan yang khusus menangani Pemasukan / Belanja Bahan Baku
        // juga nantinya akan meng-insert ke tabel ini dengan kategori berbeda.
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `transaksi_kas` (
                `id_transaksi`   INT             NOT NULL AUTO_INCREMENT,
                `tanggal`        DATE            NOT NULL,
                `tipe_transaksi` ENUM('Pemasukan', 'Pengeluaran') NOT NULL,
                `kategori`       VARCHAR(100)    NOT NULL COMMENT 'Contoh: Listrik, Mesin, Bahan Baku, Penjualan',
                `keterangan`     TEXT            DEFAULT NULL,
                `nominal`        DECIMAL(15,2)   NOT NULL DEFAULT 0.00 COMMENT 'Selalu dalam mata uang IDR (Rupiah)',
                `created_at`     DATETIME        DEFAULT CURRENT_TIMESTAMP,
                `updated_at`     DATETIME        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_transaksi`),
                KEY `idx_tanggal_kas` (`tanggal`),
                KEY `idx_tipe_kas`    (`tipe_transaksi`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // --------------------------------------------------------------------
        // 2. TABEL KARYAWAN
        // --------------------------------------------------------------------
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `karyawan` (
                `id_karyawan`       INT             NOT NULL AUTO_INCREMENT,
                `nama_karyawan`     VARCHAR(150)    NOT NULL,
                `posisi`            VARCHAR(100)    DEFAULT NULL,
                `rate_gaji_per_jam` DECIMAL(10,2)   NOT NULL DEFAULT 10000.00 COMMENT 'IDR 10.000 / jam default',
                `created_at`        DATETIME        DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_karyawan`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // --------------------------------------------------------------------
        // 3. TABEL ABSENSI
        // --------------------------------------------------------------------
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `absensi` (
                `id_absensi`      INT             NOT NULL AUTO_INCREMENT,
                `id_karyawan`     INT             NOT NULL,
                `tanggal`         DATE            NOT NULL,
                `jam_masuk`       TIME            DEFAULT NULL,
                `jam_keluar`      TIME            DEFAULT NULL,
                `total_jam_kerja` DECIMAL(5,2)    DEFAULT 0.00 COMMENT 'Kalkulasi desimal. Cth: 5.5 untuk 5 jam 30 menit',
                `created_at`      DATETIME        DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_absensi`),
                KEY `idx_karyawan_tgl` (`id_karyawan`, `tanggal`),
                CONSTRAINT `fk_absensi_karyawan`
                    FOREIGN KEY (`id_karyawan`)
                    REFERENCES `karyawan` (`id_karyawan`)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // --------------------------------------------------------------------
        // 4. TABEL PENGGAJIAN
        // --------------------------------------------------------------------
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `penggajian` (
                `id_penggajian`     INT             NOT NULL AUTO_INCREMENT,
                `id_karyawan`       INT             NOT NULL,
                `periode_bulan`     VARCHAR(20)     NOT NULL COMMENT 'Format YYYY-MM',
                `total_jam`         DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
                `total_gaji`        DECIMAL(15,2)   NOT NULL DEFAULT 0.00 COMMENT 'Total nominal (IDR)',
                `status_pembayaran` ENUM('Belum Dibayar', 'Sudah Dibayar') NOT NULL DEFAULT 'Belum Dibayar',
                `tanggal_bayar`     DATETIME        DEFAULT NULL,
                `created_at`        DATETIME        DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_penggajian`),
                CONSTRAINT `fk_penggajian_karyawan`
                    FOREIGN KEY (`id_karyawan`)
                    REFERENCES `karyawan` (`id_karyawan`)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE IF EXISTS `penggajian`');
        $this->db->query('DROP TABLE IF EXISTS `absensi`');
        $this->db->query('DROP TABLE IF EXISTS `karyawan`');
        $this->db->query('DROP TABLE IF EXISTS `transaksi_kas`');
    }
}
