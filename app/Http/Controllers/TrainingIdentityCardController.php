<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\ParticipantIdentityCard;
use App\Models\Training;
use App\Models\TrainingIdentityCardSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TrainingIdentityCardController extends Controller
{
    public function settings(Training $training)
    {
        $this->authorizeManager($training);
        $setting = $this->setting($training);
        $sample = $training->participants()->with(['pasFotoFile', 'user'])->where('registration_status', 'approved')->first()
            ?? new Participant(['name' => 'Nama Lengkap Peserta', 'instansi' => 'Instansi Peserta', 'jabatan' => 'Jabatan Peserta']);

        return view('trainings.identity-card-settings', compact('training', 'setting', 'sample'));
    }

    public function updateSettings(Request $request, Training $training)
    {
        $this->authorizeManager($training);
        $data = $request->validate([
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'background' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'background_opacity' => ['required', 'integer', 'min:0', 'max:100'],
            'enabled' => ['nullable', Rule::in(['1'])],
            'remove_logo' => ['nullable', Rule::in(['1'])],
            'remove_background' => ['nullable', Rule::in(['1'])],
        ]);
        $setting = $this->setting($training);
        if ($request->boolean('remove_logo')) $this->removeAsset($setting, 'logo_path');
        if ($request->boolean('remove_background')) $this->removeAsset($setting, 'background_path');
        if ($request->hasFile('logo')) {
            $this->removeAsset($setting, 'logo_path');
            $setting->logo_path = $request->file('logo')->store('identity-cards/'.$training->id, 'public');
        }
        if ($request->hasFile('background')) {
            $this->removeAsset($setting, 'background_path');
            $setting->background_path = $request->file('background')->store('identity-cards/'.$training->id, 'public');
        }
        $setting->fill([
            'primary_color' => $data['primary_color'], 'accent_color' => $data['accent_color'],
            'text_color' => $data['text_color'], 'background_opacity' => $data['background_opacity'],
            'enabled' => $request->boolean('enabled'), 'updated_by' => Auth::id(),
        ])->save();

        return back()->with('success', 'Desain ID card berhasil disimpan.');
    }

    public function editor(Training $training)
    {
        [$participant, $setting, $card] = $this->participantContext($training);
        return view('participant.identity-card', compact('training', 'participant', 'setting', 'card'));
    }

    public function updatePhoto(Request $request, Training $training)
    {
        [$participant, $setting, $card] = $this->participantContext($training, false);
        abort_unless($setting->enabled, 403, 'ID card belum diaktifkan oleh pengelola.');
        $request->validate(['photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']]);
        if ($card->photo_path) Storage::disk('public')->delete($card->photo_path);
        $card->update([
            'photo_path' => $request->file('photo')->store('identity-cards/'.$training->id.'/participants', 'public'),
            'generated_at' => now(),
        ]);
        return back()->with('success', 'Foto ID card berhasil diperbarui tanpa mengubah foto profil utama.');
    }

    public function pdf(Training $training)
    {
        [$participant, $setting, $card] = $this->participantContext($training);
        abort_unless($setting->enabled, 403, 'ID card belum diaktifkan oleh pengelola.');
        $images = [
            'logo' => $this->dataUri($setting->logo_path),
            'background' => $this->dataUri($setting->background_path),
            'photo' => $this->dataUri($card->photo_path ?: $participant->pasFotoFile?->file_path ?: $participant->user?->profile_photo),
        ];
        $card->update(['generated_at' => now()]);
        $name = 'IDCARD_'.preg_replace('/[^A-Za-z0-9_-]/', '_', $participant->nip_nik ?: $participant->name).'.pdf';
        return Pdf::loadView('participant.identity-card-pdf', compact('training', 'participant', 'setting', 'images'))
            ->setPaper([0, 0, 260.787, 351.496])
            ->download($name);
    }

    public function image(Training $training, string $format)
    {
        abort_unless(in_array($format, ['png', 'jpg'], true), 404);
        abort_unless(extension_loaded('gd'), 503, 'Server belum mengaktifkan ekstensi GD.');
        [$participant, $setting, $card] = $this->participantContext($training);
        abort_unless($setting->enabled, 403, 'ID card belum diaktifkan oleh pengelola.');

        $width = 920; $height = 1240;
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, true);
        $primary = $this->gdColor($canvas, $setting->primary_color);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $primary);

        if ($background = $this->gdImage($setting->background_path)) {
            $layer = imagecreatetruecolor($width, $height);
            imagealphablending($layer, true);
            $this->gdCover($layer, $background, 0, 0, $width, $height);
            imagecopymerge($canvas, $layer, 0, 0, 0, 0, $width, $height, $setting->background_opacity);
            imagedestroy($layer); imagedestroy($background);
            $overlay = $this->gdAlphaColor($canvas, $setting->primary_color, 42);
            imagefilledrectangle($canvas, 0, 0, $width, $height, $overlay);
        }
        $accent = $this->gdColor($canvas, $setting->accent_color);
        imagefilledellipse($canvas, 930, 20, 430, 430, $accent);
        $softAccent = $this->gdAlphaColor($canvas, $setting->accent_color, 82);
        imagefilledellipse($canvas, -30, 1190, 330, 330, $softAccent);

        if ($logo = $this->gdImage($setting->logo_path)) {
            $this->gdContain($canvas, $logo, 260, 25, 400, 200);
            imagedestroy($logo);
        }
        $text = $this->gdColor($canvas, $setting->text_color);
        $muted = $this->gdAlphaColor($canvas, $setting->text_color, 28);
        $font = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf');
        $bold = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
        $this->gdCenteredText($canvas, 'KARTU PESERTA', 18, 260, $bold, $muted);
        $titleLines = $this->gdWrap((string) $training->nama_pelatihan, 34, 760, $bold);
        $titleY = 305;
        foreach ($titleLines as $line) { $this->gdCenteredText($canvas, mb_strtoupper($line), 34, $titleY, $bold, $text); $titleY += 43; }

        $photoPath = $card->photo_path ?: $participant->pasFotoFile?->file_path ?: $participant->user?->profile_photo;
        imagefilledrectangle($canvas, 296, 397, 624, 833, imagecolorallocate($canvas, 255, 255, 255));
        if ($photo = $this->gdImage($photoPath)) {
            $this->gdCover($canvas, $photo, 308, 409, 304, 412);
            imagedestroy($photo);
        }
        $this->gdCenteredText($canvas, 'PESERTA', 16, 880, $bold, $muted);
        $nameLines = $this->gdWrap(mb_strtoupper((string) $participant->name), 36, 800, $bold);
        $nameY = 925;
        foreach ($nameLines as $line) { $this->gdCenteredText($canvas, $line, 36, $nameY, $bold, $text); $nameY += 46; }
        imagefilledrectangle($canvas, 420, $nameY + 2, 500, $nameY + 8, $accent);
        $this->gdCenteredText($canvas, (string) ($participant->jabatan ?: 'Peserta Pelatihan'), 19, $nameY + 48, $font, $text);
        $this->gdCenteredText($canvas, (string) ($participant->instansi ?: 'BPSDM Provinsi Jawa Barat'), 17, $nameY + 82, $font, $text);
        $footerColor = $this->gdAlphaColor($canvas, $setting->text_color, 20);
        imagefilledrectangle($canvas, 70, 1145, 850, 1147, $footerColor);
        $period = \Carbon\Carbon::parse($training->tgl_mulai)->translatedFormat('d M').' - '.\Carbon\Carbon::parse($training->tgl_selesai)->translatedFormat('d M Y');
        imagettftext($canvas, 14, 0, 70, 1187, $text, $font, $period);
        $location = (string) ($training->lokasi ?: 'BPSDM Provinsi Jawa Barat');
        $box = imagettfbbox(14, 0, $font, $location);
        imagettftext($canvas, 14, 0, max(70, 850 - ($box[2] - $box[0])), 1187, $text, $font, $location);

        ob_start();
        if ($format === 'png') imagepng($canvas, null, 6); else imagejpeg($canvas, null, 92);
        $binary = ob_get_clean(); imagedestroy($canvas);
        $card->update(['generated_at' => now()]);
        $filename = 'IDCARD_'.preg_replace('/[^A-Za-z0-9_-]/', '_', $participant->nip_nik ?: $participant->name).'.'.$format;
        return response($binary, 200, [
            'Content-Type' => $format === 'png' ? 'image/png' : 'image/jpeg',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => strlen($binary),
        ]);
    }

    private function gdImage(?string $path)
    {
        if (!$path || !Storage::disk('public')->exists($path)) return null;
        return @imagecreatefromstring(Storage::disk('public')->get($path)) ?: null;
    }

    private function hexRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    private function gdColor($canvas, string $hex): int
    {
        return imagecolorallocate($canvas, ...$this->hexRgb($hex));
    }

    private function gdAlphaColor($canvas, string $hex, int $alpha): int
    {
        [$red, $green, $blue] = $this->hexRgb($hex);
        return imagecolorallocatealpha($canvas, $red, $green, $blue, $alpha);
    }

    private function gdCover($destination, $source, int $x, int $y, int $width, int $height): void
    {
        $sw = imagesx($source); $sh = imagesy($source);
        $scale = max($width / $sw, $height / $sh);
        $cropW = (int) round($width / $scale); $cropH = (int) round($height / $scale);
        $sx = (int) max(0, ($sw - $cropW) / 2); $sy = (int) max(0, ($sh - $cropH) / 2);
        imagecopyresampled($destination, $source, $x, $y, $sx, $sy, $width, $height, $cropW, $cropH);
    }

    private function gdContain($destination, $source, int $x, int $y, int $width, int $height): void
    {
        $scale = min($width / imagesx($source), $height / imagesy($source));
        $w = (int) round(imagesx($source) * $scale); $h = (int) round(imagesy($source) * $scale);
        imagecopyresampled($destination, $source, $x + (int)(($width - $w) / 2), $y + (int)(($height - $h) / 2), 0, 0, $w, $h, imagesx($source), imagesy($source));
    }

    private function gdCenteredText($canvas, string $text, int $size, int $baseline, string $font, int $color): void
    {
        $box = imagettfbbox($size, 0, $font, $text);
        $x = (int) ((imagesx($canvas) - ($box[2] - $box[0])) / 2);
        imagettftext($canvas, $size, 0, $x, $baseline, $color, $font, $text);
    }

    private function gdWrap(string $text, int $size, int $maxWidth, string $font): array
    {
        $lines = []; $line = '';
        foreach (preg_split('/\s+/', trim($text)) as $word) {
            $candidate = trim($line.' '.$word);
            $box = imagettfbbox($size, 0, $font, $candidate);
            if ($line !== '' && ($box[2] - $box[0]) > $maxWidth) { $lines[] = $line; $line = $word; } else { $line = $candidate; }
        }
        if ($line !== '') $lines[] = $line;
        return array_slice($lines, 0, 3);
    }
    private function participantContext(Training $training, bool $requireEnabled = true): array
    {
        $user = Auth::user();
        $participant = Participant::with(['pasFotoFile', 'user'])->where('training_id', $training->id)
            ->where(fn ($q) => $q->where('user_id', $user->id)->when(filled($user->nip_nik), fn ($x) => $x->orWhere('nip_nik', $user->nip_nik)))
            ->where('registration_status', 'approved')->firstOrFail();
        $setting = $this->setting($training);
        if ($requireEnabled) abort_unless($setting->enabled, 403, 'ID card belum diaktifkan oleh pengelola.');
        $card = ParticipantIdentityCard::firstOrCreate(['participant_id' => $participant->id]);
        return [$participant, $setting, $card];
    }

    private function setting(Training $training): TrainingIdentityCardSetting
    {
        return TrainingIdentityCardSetting::firstOrCreate(['training_id' => $training->id]);
    }

    private function authorizeManager(Training $training): void
    {
        $user = Auth::user();
        abort_unless($user->role === 'superadmin' || ($user->role === 'admin_bidang' && $user->bidang === $training->bidang), 403);
    }

    private function removeAsset(TrainingIdentityCardSetting $setting, string $field): void
    {
        if ($setting->{$field}) Storage::disk('public')->delete($setting->{$field});
        $setting->{$field} = null;
    }

    private function dataUri(?string $path): ?string
    {
        if (!$path || !Storage::disk('public')->exists($path)) return null;
        $mime = Storage::disk('public')->mimeType($path) ?: 'image/jpeg';
        return 'data:'.$mime.';base64,'.base64_encode(Storage::disk('public')->get($path));
    }
}