<?php

namespace App\Imports;

use App\Models\Participant;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ParticipantImport implements ToModel, WithHeadingRow
{
    private $training_id;

    public function __construct($training_id)
    {
        $this->training_id = $training_id;
    }

    public function model(array $row)
    {
        // Bersihkan tanda kutip satu di awal jika ada
        $nip = ltrim($row['nip_nik'], "'");

        $exists = Participant::where('training_id', $this->training_id)
            ->where('nip_nik', $nip)
            ->exists();

        if ($exists) {
            return null;
        }

        return new Participant([
            'training_id' => $this->training_id,
            'nip_nik'     => $nip, // Simpan NIP yang sudah bersih
            'name'        => $row['nama_lengkap'],
            'jabatan'     => $row['jabatan'],
            'instansi'    => $row['instansi'],
        ]);
    }
}