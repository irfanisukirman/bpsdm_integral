<?php

namespace App\Http\Controllers;

use App\Models\CertificationEvent;
use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;

class PublicCertificationSpeakerController extends Controller
{
    private const BIDANG = 'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan';

    public function form(string $token)
    {
        $event = $this->event($token);

        return view('certifications.public_speaker_form', compact('event'));
    }

    public function submit(Request $request, string $token)
    {
        $event = $this->event($token);
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'required_without:nik', 'string', 'max:80'],
            'nik' => ['nullable', 'required_without:nip', 'string', 'max:80'],
            'tempat_tgllahir' => ['required', 'string', 'max:255'],
            'pangkat_golongan' => ['nullable', 'string', 'max:255'],
            'jabatan' => ['required', 'string', 'max:255'],
            'instansi' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:Laki-laki,Perempuan'],
            'alamat_rumah' => ['required', 'string', 'max:2000'],
            'nomor_rekening' => ['required', 'string', 'max:100'],
            'nama_bank' => ['required', 'string', 'max:150'],
            'nama_sesuai_rekening' => ['required', 'string', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:100'],
            'signature_data' => ['required', 'string', 'max:2000000'],
        ], [
            'nip.required_without' => 'Isi salah satu NIP atau NIK.',
            'nik.required_without' => 'Isi salah satu NIP atau NIK.',
            'signature_data.required' => 'Tanda tangan wajib dibubuhkan.',
        ]);

        $signature = $this->decodeSignature($data['signature_data']);
        $temporaryDirectory = storage_path('app/temp-certifications');
        if (! is_dir($temporaryDirectory)) {
            mkdir($temporaryDirectory, 0775, true);
        }

        $uuid = Str::uuid()->toString();
        $signaturePath = $temporaryDirectory.'/'.$uuid.'.png';
        $documentPath = $temporaryDirectory.'/'.$uuid.'.docx';
        file_put_contents($signaturePath, $signature);

        try {
            $templatePath = public_path('templates/template_biodata_pengawas.docx');
            abort_unless(is_file($templatePath), 500, 'Template biodata narasumber tidak tersedia.');

            $template = new TemplateProcessor($templatePath);
            $template->setMacroChars('{', '}');
            $identifier = filled($data['nip']) ? $data['nip'] : $data['nik'];
            $values = [
                'nama_kegiatan' => $event->title,
                'nama' => $data['nama'],
                'nip' => $data['nip'] ?: '-',
                'nik' => $data['nik'] ?: '-',
                'nip/nik' => $identifier,
                'tempat_tgllahir' => $data['tempat_tgllahir'],
                'pangkat_golongan' => $data['pangkat_golongan'] ?: '-',
                'jabatan' => $data['jabatan'],
                'instansi' => $data['instansi'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'alamat_rumah' => $data['alamat_rumah'],
                'nomor_rekening' => $data['nomor_rekening'],
                'nama_bank' => $data['nama_bank'],
                'nama_sesuai_rekening' => $data['nama_sesuai_rekening'],
                'npwp' => $data['npwp'] ?: '-',
                'tanggal_buat' => now('Asia/Jakarta')->translatedFormat('d F Y'),
            ];
            foreach ($values as $key => $value) {
                $template->setValue($key, $this->safe($value));
            }
            $template->setImageValue('tandatangan', [
                'path' => $signaturePath,
                'width' => 150,
                'height' => 65,
                'ratio' => true,
            ]);
            $template->saveAs($documentPath);
            abort_unless(is_file($documentPath) && filesize($documentPath) > 1000, 500, 'Dokumen biodata gagal dibuat.');

            $folder = Folder::firstOrCreate([
                'name' => 'Biodata Narasumber',
                'parent_id' => $event->folder_id,
                'bidang' => self::BIDANG,
            ], [
                'user_id' => $event->created_by,
                'is_public' => false,
            ]);
            $filename = 'Biodata Narasumber - '.$this->filename($data['nama']).' - '.$this->filename($identifier).'.docx';
            $storagePath = 'documents/certification-speakers/'.$uuid.'.docx';
            Storage::disk('public')->put($storagePath, file_get_contents($documentPath));

            try {
                $file = File::create([
                    'folder_id' => $folder->id,
                    'display_name' => $filename,
                    'file_path' => $storagePath,
                    'file_type' => 'docx',
                    'file_size' => Storage::disk('public')->size($storagePath),
                    'user_id' => $event->created_by,
                ]);
            } catch (\Throwable $exception) {
                Storage::disk('public')->delete($storagePath);
                throw $exception;
            }
        } finally {
            if (isset($template)) {
                $template->setMacroChars('${', '}');
            }
            @unlink($signaturePath);
            @unlink($documentPath);
        }

        return redirect()->route('certifications.speakers.public', $event->public_token)
            ->with('success', 'Biodata narasumber berhasil dibuat dan tersimpan pada dokumen kegiatan sertifikasi.')
            ->with('speaker_file_id', $file->id);
    }

    private function event(string $token): CertificationEvent
    {
        return CertificationEvent::with('type')->where('public_token', $token)->firstOrFail();
    }

    private function decodeSignature(string $data): string
    {
        abort_unless(preg_match('/^data:image\/png;base64,(.+)$/', $data, $matches), 422, 'Format tanda tangan tidak valid.');
        $binary = base64_decode(str_replace(' ', '+', $matches[1]), true);
        abort_if($binary === false || strlen($binary) > 1500000, 422, 'Data tanda tangan tidak valid.');

        return $binary;
    }

    private function safe(mixed $value): string
    {
        return str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], (string) $value);
    }

    private function filename(string $value): string
    {
        return trim(preg_replace('/[^\pL\pN._-]+/u', ' ', $value));
    }
}
