<?php

namespace App\Services\ElectronicSignature;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class BsreClient
{
    public function signInvisible(string $pdfPath, string $nik, string $passphrase): string
    {
        return $this->sign($pdfPath, $nik, $passphrase, ['tampilan' => 'invisible']);
    }

    public function signVisible(string $pdfPath, string $nik, string $passphrase, array $appearance): string
    {
        $required = ['page', 'xAxis', 'yAxis', 'width', 'height'];
        foreach ($required as $key) {
            if (!array_key_exists($key, $appearance)) {
                throw new ElectronicSignatureException("Parameter tampilan {$key} belum tersedia.");
            }
        }

        return $this->sign($pdfPath, $nik, $passphrase, [
            'tampilan' => 'visible',
            'image' => $appearance['image'] ?? 'false',
            'linkQR' => $appearance['linkQR'] ?? '',
            'page' => (int) $appearance['page'],
            'xAxis' => (int) $appearance['xAxis'],
            'yAxis' => (int) $appearance['yAxis'],
            'width' => (int) $appearance['width'],
            'height' => (int) $appearance['height'],
        ]);
    }

    private function sign(string $pdfPath, string $nik, string $passphrase, array $payload): string
    {
        $this->ensureConfigured();
        if (!is_file($pdfPath) || !is_readable($pdfPath)) {
            throw new ElectronicSignatureException('File PDF yang akan ditandatangani tidak ditemukan.');
        }
        if (trim($nik) === '' || trim($passphrase) === '') {
            throw new ElectronicSignatureException('NIK dan passphrase BSrE wajib diisi.');
        }

        try {
            $response = $this->request()
                ->attach('file', file_get_contents($pdfPath), basename($pdfPath))
                ->post(rtrim((string) config('services.bsre.url'), '/'), array_merge($payload, [
                    'nik' => trim($nik),
                    'passphrase' => $passphrase,
                ]));
        } catch (ConnectionException $exception) {
            throw new ElectronicSignatureException(
                'Server BSrE tidak dapat dihubungi. Pastikan server INTEGRAL terhubung ke jaringan internal/VPN BSrE dan alamat layanan pada konfigurasi masih aktif.',
                0,
                $exception
            );
        }

        $body = $response->body();
        if ($response->successful() && str_starts_with($body, '%PDF-')) {
            return $body;
        }

        $json = $response->json();
        $status = is_array($json) ? ($json['status_code'] ?? $json['status'] ?? $response->status()) : $response->status();
        $message = is_array($json) ? ($json['error'] ?? $json['message'] ?? 'BSrE menolak permintaan.') : 'Respons BSrE bukan PDF yang valid.';
        throw new ElectronicSignatureException('BSrE '.$status.': '.str($message)->limit(500));
    }

    private function request(): PendingRequest
    {
        return Http::withBasicAuth((string) config('services.bsre.username'), (string) config('services.bsre.password'))
            ->accept('application/pdf, application/json')
            ->timeout((int) config('services.bsre.timeout', 120))
            ->connectTimeout(15)
            ->withOptions(['verify' => (bool) config('services.bsre.verify_ssl', true)]);
    }

    private function ensureConfigured(): void
    {
        foreach (['url', 'username', 'password'] as $key) {
            if (blank(config("services.bsre.{$key}"))) {
                throw new ElectronicSignatureException("Konfigurasi BSRE_{$key} belum tersedia.");
            }
        }
    }
}
