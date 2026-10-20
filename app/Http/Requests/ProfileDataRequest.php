<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Basis validasi data profil yang dipakai bersama oleh:
 * - Halaman pendaftaran publik (RegisterRequest)
 * - Halaman pelengkapan profil peserta (CompleteProfileRequest)
 *
 * Tujuannya supaya aturan NIP/NIK, wilayah, dan koordinat hanya ada di satu tempat.
 */
abstract class ProfileDataRequest extends FormRequest
{
    /**
     * Daftar golongan yang sah sesuai data kepegawaian.
     */
    public const GOLONGAN = [
        'I/a', 'II/a', 'II/b', 'II/c', 'II/d',
        'III/a', 'III/b', 'III/c', 'III/d',
        'IV/a', 'IV/b', 'IV/c',
        'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 'XIII', 'XIV',
    ];

    public const USER_TYPES = ['peserta', 'narasumber', 'mitra'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * User yang sedang dilayani. Pada pendaftaran selalu null karena akun baru.
     */
    protected function targetUser(): ?User
    {
        return $this->user();
    }

    /**
     * Aturan profil yang selalu wajib diisi.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function profileRules(): array
    {
        $userId = $this->targetUser()?->id;

        return [
            'user_type' => ['required', Rule::in(self::USER_TYPES)],
            'nip_nik' => ['required', 'string', 'max:255', Rule::unique('users', 'nip_nik')->ignore($userId)],
            'whatsapp' => ['required', 'string', 'max:30'],
            'gender' => ['required'],
            'status_kepegawaian' => ['required', Rule::in(['PNS', 'PPPK', 'PPPK-PW'])],
            'birth_place' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'jabatan' => ['required', 'string', 'max:255'],
            'golongan' => ['nullable', Rule::in(self::GOLONGAN)],
            'instansi' => ['required', 'string', 'max:255'],
            'provinsi' => ['required', 'string', 'max:255'],
            'kota' => ['required', 'string', 'max:255'],
            'kecamatan' => ['required', 'string', 'max:255'],
            'kelurahan' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_type.required' => 'Pilih tujuan pendaftaran.',
            'user_type.in' => 'Pilih tujuan pendaftaran yang tersedia.',
            'nip_nik.unique' => 'NIP/NIK ini sudah terdaftar di sistem. Gunakan NIP/NIK lain atau hubungi pengelola.',
            'nip_nik.required' => 'NIP/NIK wajib diisi.',
            'nip_nik.max' => 'NIP/NIK maksimal 255 karakter.',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp.max' => 'Nomor WhatsApp maksimal 30 digit.',
            'gender.required' => 'Pilih jenis kelamin.',
            'status_kepegawaian.required' => 'Pilih status kepegawaian.',
            'status_kepegawaian.in' => 'Status kepegawaian yang dipilih tidak sesuai daftar yang berlaku.',
            'birth_place.required' => 'Tempat lahir wajib diisi.',
            'birth_date.required' => 'Tanggal lahir wajib diisi.',
            'birth_date.date' => 'Tanggal lahir tidak valid.',
            'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh melewati hari ini.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'jabatan.max' => 'Jabatan maksimal 255 karakter.',
            'golongan.in' => 'Golongan yang dipilih tidak sesuai daftar yang berlaku.',
            'instansi.required' => 'Instansi wajib diisi.',
            'instansi.max' => 'Instansi maksimal 255 karakter.',
            'provinsi.required' => 'Provinsi wajib diisi.',
            'kota.required' => 'Kota/Kabupaten wajib diisi.',
            'kecamatan.required' => 'Kecamatan wajib diisi.',
            'kelurahan.required' => 'Kelurahan wajib diisi.',
            'address.required' => 'Alamat wajib diisi.',
            'address.max' => 'Alamat maksimal 1000 karakter.',
            'latitude.required' => 'Titik latitude belum terisi. Pilih lokasi pada peta atau cari lewat kolom alamat.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'latitude.between' => 'Titik latitude berada di luar jangkauan.',
            'longitude.required' => 'Titik longitude belum terisi. Pilih lokasi pada peta atau cari lewat kolom alamat.',
            'longitude.numeric' => 'Longitude harus berupa angka.',
            'longitude.between' => 'Titik longitude berada di luar jangkauan.',
        ];
    }

    /**
     * Nama field dalam bahasa Indonesia agar pesan bawaan Laravel tidak
     * pernah jatuh ke kalimat Inggris seperti "The gender field is required."
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'email' => 'email',
            'email_confirmation' => 'konfirmasi email',
            'username' => 'email',
            'password' => 'password',
            'password_confirmation' => 'konfirmasi password',
            'user_type' => 'tujuan pendaftaran',
            'nip_nik' => 'NIP/NIK',
            'whatsapp' => 'nomor WhatsApp',
            'gender' => 'jenis kelamin',
            'status_kepegawaian' => 'status kepegawaian',
            'birth_place' => 'tempat lahir',
            'birth_date' => 'tanggal lahir',
            'jabatan' => 'jabatan',
            'golongan' => 'golongan',
            'instansi' => 'instansi',
            'provinsi' => 'provinsi',
            'kota' => 'kota/kabupaten',
            'kecamatan' => 'kecamatan',
            'kelurahan' => 'kelurahan',
            'address' => 'alamat',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
        ];
    }

    /**
     * Bersihkan input sebelum divalidasi.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['name', 'nip_nik', 'whatsapp', 'jabatan', 'instansi', 'address'] as $field) {
            if (is_string($this->input($field))) {
                $merge[$field] = trim($this->input($field));
            }
        }

        if ($this->filled('whatsapp')) {
            $merge['whatsapp'] = preg_replace('/\D+/', '', $merge['whatsapp'] ?? $this->input('whatsapp'));
        }

        if ($merge) {
            $this->merge($merge);
        }
    }
}