<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\User;

/**
 * Menulis data profil (identitas, kepegawaian, wilayah, titik lokasi) ke user
 * sekaligus menyinkronkannya ke tabel participants.
 *
 * Dipakai oleh alur pendaftaran publik maupun pelengkapan profil peserta
 * supaya hasilnya konsisten di kedua tempat.
 */
class ProfileDataService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function apply(User $user, array $data): User
    {
        $requestedType = $this->resolveUserType($user, $data['user_type'] ?? 'peserta');

        $user->fill([
            'user_type' => $requestedType,
            // Narasumber langsung aktif, akun mitra menunggu persetujuan superadmin.
            'user_type_status' => in_array($requestedType, ['peserta', 'narasumber'], true) ? 'approved' : 'pending',
            'role' => $requestedType === 'narasumber' ? 'pengajar' : 'participant',
            'bidang' => null,
            'nip_nik' => $data['nip_nik'],
            'whatsapp' => $data['whatsapp'],
            'gender' => $data['gender'],
            'birth_place' => $data['birth_place'],
            'birth_date' => $data['birth_date'],
            'jabatan' => $data['jabatan'],
            'golongan' => $data['golongan'] ?? null,
            'instansi' => $data['instansi'],
            'status_kepegawaian' => $data['status_kepegawaian'],
            'provinsi' => $data['provinsi'],
            'kota' => $data['kota'],
            'kecamatan' => $data['kecamatan'],
            'kelurahan' => $data['kelurahan'],
            'address' => $data['address'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'must_complete_profile' => false,
            'must_change_password' => false,
        ]);

        // Password hanya diganti bila benar-benar diisi; cast 'hashed' milik model
        // yang akan melakukan hashing otomatis.
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        $this->syncParticipant($user);

        return $user;
    }

    /**
     * Akun hasil impor peserta tidak boleh memilih tipe lain.
     */
    private function resolveUserType(User $user, string $requested): string
    {
        if ($user->exists && $user->must_complete_profile) {
            return 'peserta';
        }

        return in_array($requested, ['peserta', 'narasumber', 'mitra'], true) ? $requested : 'peserta';
    }

    /**
     * Sinkronisasi ke tabel participants memakai NIP/NIK sebagai kunci riwayat.
     *
     * Baris peserta sudah ada untuk akun hasil impor, jadi di sini hanya
     * memperbarui isinya. Pendaftaran publik belum punya baris dan sengaja
     * tidak dibuat di sini karena baris peserta lahir saat peserta mendaftar
     * pelatihan.
     *
     * Perhatikan perbedaan nama kolom: users memakai "kota", sedangkan
     * participants memakai "kabupaten_kota".
     */
    private function syncParticipant(User $user): void
    {
        Participant::where('nip_nik', $user->nip_nik)->update([
            'user_id' => $user->id,
            'name' => $user->name,
            'phone' => $user->whatsapp,
            'gender' => $user->gender,
            'jabatan' => $user->jabatan,
            'instansi' => $user->instansi,
            'provinsi' => $user->provinsi,
            'kabupaten_kota' => $user->kota,
            'kecamatan' => $user->kecamatan,
            'kelurahan' => $user->kelurahan,
            'status_kepegawaian' => $user->status_kepegawaian,
        ]);
    }
}