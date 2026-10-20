<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Validasi form pendaftaran publik yang terbagi per section:
 * akun, identitas & kepegawaian, wilayah, dan titik lokasi.
 */
class RegisterRequest extends ProfileDataRequest
{
    /**
     * Pendaftaran selalu membuat akun baru, jadi tidak ada user yang perlu diabaikan
     * pada aturan unique.
     */
    protected function targetUser(): ?User
    {
        return null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return array_merge($this->profileRules(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'email_confirmation' => ['required', 'same:email'],
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email belum benar.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk menggunakan akun yang sudah ada.',
            'username.required' => 'Email wajib diisi.',
            'username.unique' => 'Email ini sudah dipakai akun lain. Silakan gunakan email lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama dengan password baru.',
            'email_confirmation.required' => 'Konfirmasi email wajib diisi.',
            'email_confirmation.same' => 'Konfirmasi email tidak sama dengan email.',
        ]);
    }

    /**
     * Samakan username dengan email agar keduanya bisa dipakai untuk login,
     * sekaligus mencegah duplikasi dengan akun yang dibuat lewat Google.
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $email = $this->input('email');

        if (is_string($email)) {
            $normalized = Str::lower(trim($email));

            $this->merge([
                'email' => $normalized,
                'username' => $normalized,
                'email_confirmation' => is_string($this->input('email_confirmation'))
                    ? Str::lower(trim($this->input('email_confirmation')))
                    : $this->input('email_confirmation'),
            ]);
        }
    }

    /**
     * Data siap simpan untuk kolom users.
     *
     * @return array<string, mixed>
     */
    public function accountData(): array
    {
        return [
            'name' => $this->validated('name'),
            'email' => $this->validated('email'),
            'username' => $this->validated('username'),
            'password' => $this->validated('password'),
        ];
    }
}