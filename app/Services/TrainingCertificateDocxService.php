<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\Training;
use App\Models\TrainingCertificateSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\Process\Process;

class TrainingCertificateDocxService
{
    public const CODES = [
        'nama', 'nip_nik', 'jabatan', 'instansi', 'foto', 'nomor_sertifikat',
        'nama_pelatihan', 'tanggal_mulai', 'tanggal_selesai', 'tanggal_sertifikat',
    ];

    public function validateTemplate(string $path): array
    {
        try {
            $word = IOFactory::load($path, 'Word2007');
            $sections = $word->getSections();
            if ($sections === []) throw new \RuntimeException('Dokumen tidak mempunyai halaman.');
            foreach ($sections as $section) {
                $style = $section->getStyle();
                $width = (float) $style->getPageSizeW();
                $height = (float) $style->getPageSizeH();
                $isLandscapeA4 = $width > $height
                    && abs($width - 16838) <= 600
                    && abs($height - 11906) <= 600;
                if (! $isLandscapeA4) {
                    throw ValidationException::withMessages([
                        'template' => 'Template harus menggunakan ukuran A4 Landscape (29,7 x 21 cm). Periksa Size dan Orientation pada menu Layout di Word.',
                    ]);
                }
            }

            $variables = (new TemplateProcessor($path))->getVariables();
            $unknown = array_values(array_diff($variables, self::CODES));
            $missing = array_values(array_diff(['nama', 'nomor_sertifikat'], $variables));
            if ($unknown !== []) {
                throw ValidationException::withMessages([
                    'template' => 'Kode template tidak dikenal: '.collect($unknown)->map(fn ($code) => '${'.$code.'}')->join(', ').'.',
                ]);
            }
            if ($missing !== []) {
                throw ValidationException::withMessages([
                    'template' => 'Template wajib memuat kode '.collect($missing)->map(fn ($code) => '${'.$code.'}')->join(' dan ').'.',
                ]);
            }

            return $variables;
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'template' => 'Template DOCX tidak dapat dibaca. Pastikan file tidak rusak dan disimpan sebagai dokumen Word .docx.',
            ]);
        }
    }

    public function render(
        string $templatePath,
        string $outputPdf,
        Training $training,
        Participant $participant,
        TrainingCertificateSetting $setting,
        string $certificateNumber
    ): void {
        $this->validateTemplate($templatePath);
        $template = new TemplateProcessor($templatePath);
        $variables = $template->getVariables();
        $values = [
            'nama' => $participant->name,
            'nip_nik' => $participant->nip_nik,
            'jabatan' => $participant->jabatan ?: $participant->user?->jabatan ?: '-',
            'instansi' => $participant->instansi ?: $participant->user?->instansi ?: '-',
            'nomor_sertifikat' => $certificateNumber,
            'nama_pelatihan' => $training->nama_pelatihan,
            'tanggal_mulai' => $training->tgl_mulai ? \Carbon\Carbon::parse($training->tgl_mulai)->translatedFormat('d F Y') : '-',
            'tanggal_selesai' => $training->tgl_selesai ? \Carbon\Carbon::parse($training->tgl_selesai)->translatedFormat('d F Y') : '-',
            'tanggal_sertifikat' => $setting->issued_at->translatedFormat('d F Y'),
        ];
        foreach ($values as $key => $value) {
            if (in_array($key, $variables, true)) $template->setValue($key, htmlspecialchars((string) $value));
        }

        if (in_array('foto', $variables, true)) {
            if ($participant->pasFotoFile && Storage::disk('public')->exists($participant->pasFotoFile->file_path)) {
                $size = $setting->photo_size === '2x3' ? ['width' => 76, 'height' => 113] : ['width' => 113, 'height' => 151];
                $template->setImageValue('foto', [
                    'path' => Storage::disk('public')->path($participant->pasFotoFile->file_path),
                    'width' => $size['width'], 'height' => $size['height'], 'ratio' => false,
                ]);
            } else {
                $template->setValue('foto', 'Foto belum tersedia');
            }
        }

        $workingDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'integral-cert-'.bin2hex(random_bytes(8));
        if (! mkdir($workingDirectory, 0775, true) && ! is_dir($workingDirectory)) {
            throw new \RuntimeException('Folder sementara sertifikat tidak dapat dibuat.');
        }

        $docx = $workingDirectory.DIRECTORY_SEPARATOR.'certificate.docx';
        $template->saveAs($docx);

        try {
            $binary = $this->libreOfficeBinary();
            $converted = $workingDirectory.DIRECTORY_SEPARATOR.'certificate.pdf';
            $conversionErrors = [];

            for ($attempt = 1; $attempt <= 2; $attempt++) {
                @unlink($converted);
                $profile = str_replace('\\', '/', $workingDirectory.DIRECTORY_SEPARATOR.'profile-'.$attempt);
                $process = new Process([
                    $binary, '-env:UserInstallation=file:///'.ltrim($profile, '/'),
                    '--headless', '--convert-to', 'pdf:writer_pdf_Export',
                    '--outdir', $workingDirectory, $docx,
                ], $workingDirectory);
                $process->setEnv($this->libreOfficeEnvironment($binary));
                $process->setTimeout(120);
                $process->run();

                if ($process->isSuccessful() && is_file($converted) && filesize($converted) > 0) break;

                $details = trim($process->getErrorOutput().' '.$process->getOutput());
                $conversionErrors[] = 'percobaan '.$attempt.' (kode '.$process->getExitCode().'): '.($details ?: 'file PDF tidak terbentuk');
                if ($attempt < 2) usleep(500000);
            }

            if (! is_file($converted) || filesize($converted) === 0) {
                throw new \RuntimeException('LibreOffice gagal mengonversi template setelah dicoba ulang. '.implode(' | ', $conversionErrors));
            }
            $directory = dirname($outputPdf);
            if (! is_dir($directory)) mkdir($directory, 0775, true);
            if (! copy($converted, $outputPdf)) throw new \RuntimeException('PDF hasil konversi tidak dapat disimpan.');
        } finally {
            foreach (glob($workingDirectory.DIRECTORY_SEPARATOR.'*') ?: [] as $item) {
                if (is_file($item)) @unlink($item);
            }
            $this->removeDirectory($workingDirectory);
        }
    }

    public function libreOfficeAvailable(): bool
    {
        try {
            $this->libreOfficeBinary();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }


    private function libreOfficeEnvironment(string $binary): array
    {
        $keys = ['SystemRoot', 'WINDIR', 'USERPROFILE', 'APPDATA', 'LOCALAPPDATA', 'TEMP', 'TMP', 'PROGRAMFILES', 'PROGRAMDATA', 'COMSPEC'];
        $environment = [];
        foreach ($keys as $key) {
            $value = getenv($key);
            if ($value !== false && $value !== '') $environment[$key] = $value;
        }

        $environment['PATH'] = dirname($binary).PATH_SEPARATOR.(getenv('PATH') ?: '');
        $environment['PYTHONHOME'] = false;
        $environment['PYTHONPATH'] = false;
        $environment['SAL_USE_VCLPLUGIN'] = 'svp';

        return $environment;
    }

    private function libreOfficeBinary(): string
    {
        $configured = config('services.libreoffice.binary');
        $configuredConsole = $configured && str_ends_with(strtolower($configured), 'soffice.exe')
            ? substr($configured, 0, -3).'com'
            : null;
        $candidates = array_filter([
            $configuredConsole,
            $configured,
            'C:\Program Files\LibreOffice\program\soffice.com',
            'C:\Program Files\LibreOffice\program\soffice.exe',
            'C:\Program Files (x86)\LibreOffice\program\soffice.com',
            'C:\Program Files (x86)\LibreOffice\program\soffice.exe',
            '/usr/bin/libreoffice', '/usr/bin/soffice',
        ]);
        foreach ($candidates as $candidate) {
            if (is_file($candidate) && is_executable($candidate)) return $candidate;
        }
        throw new \RuntimeException('LibreOffice belum tersedia di server. Instal LibreOffice atau isi LIBREOFFICE_BINARY dengan lokasi soffice.');
    }

    private function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) return;
        $items = array_diff(scandir($directory) ?: [], ['.', '..']);
        foreach ($items as $item) {
            $path = $directory.DIRECTORY_SEPARATOR.$item;
            is_dir($path) ? $this->removeDirectory($path) : @unlink($path);
        }
        @rmdir($directory);
    }
}
