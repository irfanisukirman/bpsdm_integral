<?php

namespace App\Services\ElectronicSignature;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class JctClient
{
    public function templates(): array
    {
        $response = $this->request()->get($this->url('getTemplateForIntegral'));
        $response->throw();
        return (array) ($response->json('data') ?? []);
    }

    public function downloadCertificate(string $activityId, string $userId): string
    {
        $response = $this->request()->get($this->url('downloadforintegral/'.rawurlencode($activityId).'/'.rawurlencode($userId).'.pdf'));
        if (!$response->successful() || !str_starts_with($response->body(), '%PDF-')) {
            throw new ElectronicSignatureException('Jabar Corpu Talent tidak mengembalikan PDF sertifikat yang valid.');
        }
        return $response->body();
    }

    public function uploadFinalCertificate(string $activityId, string $userId, string $pdfPath): void
    {
        if (!is_file($pdfPath) || !is_readable($pdfPath)) {
            throw new ElectronicSignatureException('PDF final yang akan dikirim ke Jabar Corpu Talent tidak ditemukan.');
        }
        $response = $this->request()->attach('pdf_file', file_get_contents($pdfPath), $userId.'.pdf')
            ->post($this->url('certificateFromIntegral/'.rawurlencode($activityId).'/'.rawurlencode($userId)));
        $response->throw();
    }

    private function request(): PendingRequest
    {
        $request = Http::acceptJson()->timeout((int) config('services.jct.timeout', 30))->connectTimeout(10);
        if (filled(config('services.jct.username'))) {
            $request->withBasicAuth((string) config('services.jct.username'), (string) config('services.jct.password'));
        }
        return $request;
    }

    private function url(string $path): string
    {
        $base = rtrim((string) config('services.jct.url'), '/');
        if ($base === '') throw new ElectronicSignatureException('Konfigurasi JCT_API_URL belum tersedia.');
        return $base.'/'.ltrim($path, '/');
    }
}