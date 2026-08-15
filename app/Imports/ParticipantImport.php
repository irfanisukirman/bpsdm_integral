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
        // Bersihkan NIP dari tanda kutip
        $nip = ltrim($row['nip_nik'], "'");

        // Cari peserta di pelatihan ini berdasarkan NIP
        $participant = \App\Models\Participant::where('training_id', $this->training_id)
            ->where('nip_nik', $nip)
            ->first();

        if ($participant) {
            // Update atau buat nilai baru
            return new Participant([
                'training_id'        => $this->training_id,
                'nip_nik'            => $nip,
                'name'               => $row['nama_lengkap'],
                'gender'             => $row['gender'],
                'jabatan'            => $row['jabatan'],
                'instansi'           => $row['instansi'],
                'provinsi'           => $row['provinsi'],
                'kabupaten_kota'     => $row['kabupaten_kota'],
                'status_kepegawaian' => $row['status_kepegawaian'],
            ]);
        }

        return null;
    }
}