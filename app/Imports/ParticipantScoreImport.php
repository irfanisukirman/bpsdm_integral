<?php

namespace App\Imports;

use App\Models\Participant;
use App\Models\EvaluationResultL2;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ParticipantScoreImport implements ToModel, WithHeadingRow
{
    protected $training_id;

    public function __construct($training_id)
    {
        $this->training_id = $training_id;
    }

    public function model(array $row)
    {
        // 1. Bersihkan NIP dari tanda kutip jika ada
        $nip = ltrim($row['nip_nik'], "'");

        // 2. Cari peserta berdasarkan NIP dan ID Pelatihan
        $participant = Participant::where('training_id', $this->training_id)
            ->where('nip_nik', $nip)
            ->first();

        // 3. Jika peserta ditemukan, update atau buat nilai L2
        if ($participant) {
            return EvaluationResultL2::updateOrCreate(
                ['participant_id' => $participant->id],
                [
                    'pretest' => $row['nilai_pretest'] ?? 0,
                    'postest' => $row['nilai_posttest'] ?? 0,
                ]
            );
        }

        return null; // Lewati jika NIP tidak terdaftar di pelatihan ini
    }
}