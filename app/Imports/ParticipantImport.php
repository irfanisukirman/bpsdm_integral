<?php

namespace App\Imports;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ParticipantImport implements ToCollection, WithHeadingRow
{
    public array $results = [];
    public int $accountsCreated = 0;
    public int $participantsAdded = 0;
    public int $skipped = 0;
    public int $failed = 0;

    public function __construct(private int $trainingId) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $number = $index + 2;
            $nip = $this->normalizeIdentity($row['nip_nik'] ?? null);
            $name = trim((string) ($row['nama_lengkap'] ?? $row['nama'] ?? ''));
            $institution = trim((string) ($row['instansi'] ?? ''));

            if ($nip === '' && $name === '' && $institution === '') continue;
            if ($nip === '' || $name === '' || $institution === '') {
                $this->record($number, $nip, $name, $institution, 'Gagal', '', 'NIP/NIK, nama lengkap, dan instansi wajib diisi.');
                $this->failed++;
                continue;
            }
            if (mb_strlen($nip) > 50 || mb_strlen($name) > 255 || mb_strlen($institution) > 255) {
                $this->record($number, $nip, $name, $institution, 'Gagal', '', 'Panjang data melebihi batas sistem.');
                $this->failed++;
                continue;
            }

            try {
                DB::transaction(function () use ($number, $nip, $name, $institution) {
                    if (Participant::where('training_id', $this->trainingId)->where('nip_nik', $nip)->exists()) {
                        $this->record($number, $nip, $name, $institution, 'Dilewati', '', 'Sudah terdaftar pada pelatihan ini.');
                        $this->skipped++;
                        return;
                    }

                    $user = User::where('nip_nik', $nip)->orWhere('username', $nip)->first();
                    $plainPassword = '';
                    $accountStatus = 'Akun lama';

                    if (!$user) {
                        $plainPassword = $this->newPassword();
                        $user = User::create([
                            'name' => $name,
                            'username' => $nip,
                            'nip_nik' => $nip,
                            'instansi' => $institution,
                            'role' => 'participant',
                            'user_type' => 'peserta',
                            'user_type_status' => 'approved',
                            'password' => Hash::make($plainPassword),
                            'must_complete_profile' => true,
                            'must_change_password' => true,
                        ]);
                        $this->accountsCreated++;
                        $accountStatus = 'Akun baru dibuat';
                    } elseif (blank($user->nip_nik)) {
                        $user->update(['nip_nik' => $nip]);
                    }

                    Participant::create([
                        'training_id' => $this->trainingId,
                        'user_id' => $user->id,
                        'nip_nik' => $nip,
                        'name' => $user->name ?: $name,
                        'phone' => $user->whatsapp,
                        'gender' => $user->gender,
                        'jabatan' => $user->jabatan,
                        'instansi' => $user->instansi ?: $institution,
                        'provinsi' => $user->provinsi,
                        'kota' => $user->kota,
                        'kecamatan' => $user->kecamatan,
                        'kelurahan' => $user->kelurahan,
                        'status_kepegawaian' => $user->status_kepegawaian,
                        'registration_status' => 'approved',
                    ]);

                    $this->participantsAdded++;
                    $this->record($number, $nip, $name, $institution, 'Berhasil', $plainPassword, $accountStatus.'; peserta langsung disetujui.');
                });
            } catch (\Throwable $exception) {
                report($exception);
                $this->failed++;
                $this->record($number, $nip, $name, $institution, 'Gagal', '', 'Data tidak dapat diproses: '.$exception->getMessage());
            }
        }
    }

    private function normalizeIdentity(mixed $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/^[\'\x{2018}\x{2019}]+/u', '', $value) ?? $value;
        return preg_replace('/\s+/', '', $value) ?? $value;
    }

    private function newPassword(): string
    {
        return 'Int#'.Str::upper(Str::random(3)).random_int(1000, 9999);
    }

    private function record(int $row, string $nip, string $name, string $institution, string $status, string $password, string $note): void
    {
        $excelIdentity = $nip !== '' ? "'".$nip : '';
        $this->results[] = [$row, $excelIdentity, $name, $institution, $status, $excelIdentity, $password, $note];
    }
}