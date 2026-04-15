<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaksiKasModel extends Model
{
    protected $table            = 'transaksi_kas';
    protected $primaryKey       = 'id_transaksi';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    // Pastikan kita bisa menginput tipe dan kategori sesuai pemisahan tugas
    protected $allowedFields    = [
        'tanggal', 'tipe_transaksi', 'kategori', 'keterangan', 'nominal'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Menghitung total saldo saat ini
     * Pemasukan - Pengeluaran
     */
    public function getSaldoAkhir()
    {
        $db = \Config\Database::connect();
        $query = $db->query("
            SELECT 
                (SELECT COALESCE(SUM(nominal), 0) FROM transaksi_kas WHERE tipe_transaksi = 'Pemasukan') - 
                (SELECT COALESCE(SUM(nominal), 0) FROM transaksi_kas WHERE tipe_transaksi = 'Pengeluaran') 
            AS saldo
        ");
        $row = $query->getRow();
        return $row ? $row->saldo : 0;
    }
}
