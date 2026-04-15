<?php

namespace App\Models;

use CodeIgniter\Model;

class PenggajianModel extends Model
{
    protected $table            = 'penggajian';
    protected $primaryKey       = 'id_penggajian';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields    = [
        'id_karyawan', 'periode_bulan', 'total_jam', 'total_gaji', 'status_pembayaran', 'tanggal_bayar'
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = '';

    public function getPenggajianWithKaryawan()
    {
        return $this->select('penggajian.*, karyawan.nama_karyawan')
                    ->join('karyawan', 'karyawan.id_karyawan = penggajian.id_karyawan')
                    ->orderBy('penggajian.periode_bulan', 'DESC')
                    ->findAll();
    }
}
