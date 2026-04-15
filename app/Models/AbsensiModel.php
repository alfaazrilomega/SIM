<?php

namespace App\Models;

use CodeIgniter\Model;

class AbsensiModel extends Model
{
    protected $table            = 'absensi';
    protected $primaryKey       = 'id_absensi';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields    = [
        'id_karyawan', 'tanggal', 'jam_masuk', 'jam_keluar', 'total_jam_kerja'
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = '';
    
    public function getAbsensiWithKaryawan()
    {
        return $this->select('absensi.*, karyawan.nama_karyawan')
                    ->join('karyawan', 'karyawan.id_karyawan = absensi.id_karyawan')
                    ->orderBy('absensi.tanggal', 'DESC')
                    ->findAll();
    }
}
