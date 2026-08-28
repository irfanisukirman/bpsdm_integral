<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengajar extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'pangkat_golongan', 
        'instansi', 
        'npwp', 
        'nomor_rekening', 
        'nama_bank', 
        'nama_rekening',
        'cv_path',            
        'sertifikat_path',    
        'surat_tugas_path'   
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}