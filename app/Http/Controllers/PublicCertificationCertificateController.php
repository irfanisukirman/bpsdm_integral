<?php

namespace App\Http\Controllers;

use App\Models\CertificationEvent;
use App\Models\CertificationParticipant;
use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicCertificationCertificateController extends Controller
{
    private const BIDANG = 'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan';

    public function index(string $token)
    {
        $event = $this->event($token);

        return view('certifications.public_certificate_lookup', compact('event'));
    }

    public function verify(Request $request, string $token)
    {
        $event = $this->event($token);
        $data = $request->validate(['nip_nik' => ['required', 'string', 'max:80']]);
        $participant = $event->participants()->where('nip_nik', trim(ltrim($data['nip_nik'], "'")))->first();

        if (! $participant) {
            return back()->withErrors(['nip_nik' => 'NIP/NIK tidak ditemukan pada kegiatan sertifikasi ini.'])->withInput();
        }
        if ($participant->result !== 'lulus') {
            return back()->withErrors(['nip_nik' => 'Pengumpulan sertifikat hanya tersedia untuk peserta yang telah dinyatakan lulus.'])->withInput();
        }

        return redirect()->route('certifications.certificates.public.form', [$event->public_token, $participant->biodata_token]);
    }

    public function form(string $token, string $participantToken)
    {
        [$event, $participant] = $this->participant($token, $participantToken);
        $participant->load('certificateFile');

        return view('certifications.public_certificate_form', compact('event', 'participant'));
    }

    public function submit(Request $request, string $token, string $participantToken)
    {
        [$event, $participant] = $this->participant($token, $participantToken);
        $data = $request->validate([
            'certificate_number' => ['required', 'string', 'max:255'],
            'certificate_file' => [$participant->certificate_file_id ? 'nullable' : 'required', 'nullable', 'file', 'mimes:pdf', 'max:20480'],
            'certification_rating' => ['required', 'integer', 'between:1,5'],
            'certification_feedback' => ['nullable', 'string', 'max:5000'],
        ], [
            'certificate_file.required' => 'File sertifikat PDF wajib diunggah.',
            'certificate_file.mimes' => 'Sertifikat harus berupa file PDF.',
            'certification_rating.between' => 'Penilaian harus berada pada skala 1 sampai 5.',
        ]);

        $oldFile = $participant->certificateFile;
        $newFile = null;
        if ($request->hasFile('certificate_file')) {
            $folder = Folder::firstOrCreate([
                'name' => 'Sertifikat Peserta',
                'parent_id' => $event->folder_id,
                'bidang' => self::BIDANG,
            ], ['user_id' => $event->created_by, 'is_public' => false]);
            $upload = $request->file('certificate_file');
            $path = $upload->store('documents/certification-certificates', 'public');
            try {
                $newFile = File::create([
                    'folder_id' => $folder->id,
                    'display_name' => 'Sertifikat - '.$this->filename($participant->name).' - '.$this->filename($participant->nip_nik).'.pdf',
                    'file_path' => $path,
                    'file_type' => 'pdf',
                    'file_size' => $upload->getSize(),
                    'user_id' => $event->created_by,
                ]);
            } catch (\Throwable $exception) {
                Storage::disk('public')->delete($path);
                throw $exception;
            }
        }

        try {
            DB::transaction(function () use ($participant, $data, $newFile) {
                $participant->update([
                    'certificate_number' => $data['certificate_number'],
                    'certificate_file_id' => $newFile?->id ?? $participant->certificate_file_id,
                    'certification_rating' => $data['certification_rating'],
                    'certification_feedback' => $data['certification_feedback'] ?? null,
                    'certificate_submitted_at' => now(),
                ]);
            });
        } catch (\Throwable $exception) {
            if ($newFile) {
                Storage::disk('public')->delete($newFile->file_path);
                $newFile->delete();
            }
            throw $exception;
        }

        if ($newFile && $oldFile && $oldFile->id !== $newFile->id) {
            Storage::disk('public')->delete($oldFile->file_path);
            $oldFile->delete();
        }

        return redirect()->route('certifications.certificates.public.form', [$event->public_token, $participant->biodata_token])
            ->with('success', 'Sertifikat dan evaluasi berhasil disimpan. Terima kasih atas penilaian Anda.');
    }

    private function event(string $token): CertificationEvent
    {
        return CertificationEvent::with('type')->where('public_token', $token)->firstOrFail();
    }

    private function participant(string $token, string $participantToken): array
    {
        $event = $this->event($token);
        $participant = $event->participants()->where('biodata_token', $participantToken)->firstOrFail();
        abort_unless($participant->result === 'lulus', 403, 'Form ini hanya tersedia untuk peserta yang dinyatakan lulus.');

        return [$event, $participant];
    }

    private function filename(string $value): string
    {
        return trim(preg_replace('/[^\pL\pN._-]+/u', ' ', Str::limit($value, 100, '')));
    }
}
