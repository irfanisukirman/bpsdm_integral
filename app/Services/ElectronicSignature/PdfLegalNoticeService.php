<?php

namespace App\Services\ElectronicSignature;

use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class PdfLegalNoticeService
{
    private const NOTICE = 'Dokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang diterbitkan oleh Balai Besar Sertifikasi Elektronik (BSrE) Badan Siber dan Sandi Negara.';

    private const BSRE_QR_POSITION_POINTS = 24;

    private const BSRE_QR_SIZE_POINTS = 85;

    private const POINTS_PER_INCH = 72;

    private const MILLIMETRES_PER_INCH = 25.4;

    public function stamp(string $sourcePath, ?string $verificationUrl = null): string
    {
        if (!is_file($sourcePath) || !is_readable($sourcePath)) {
            throw new ElectronicSignatureException('PDF sumber untuk pemberian catatan BSrE tidak dapat dibaca.');
        }

        $tempDirectory = storage_path('app/temp-electronic-signatures');
        if (!is_dir($tempDirectory) && !mkdir($tempDirectory, 0775, true) && !is_dir($tempDirectory)) {
            throw new ElectronicSignatureException('Direktori sementara TTE tidak dapat dibuat.');
        }

        $outputPath = $tempDirectory.'/legal-notice-'.bin2hex(random_bytes(12)).'.pdf';

        try {
            $pdf = new Mpdf(['tempDir' => $tempDirectory]);
            $pageCount = $pdf->SetSourceFile($sourcePath);
            for ($page = 1; $page <= $pageCount; $page++) {
                $template = $pdf->ImportPage($page);
                $size = $pdf->getTemplateSize($template);
                $width = (float) $size['width'];
                $height = (float) $size['height'];
                $isLandscape = $width > $height;
                $pdf->AddPageByArray(['orientation' => $isLandscape ? 'L' : 'P', 'sheet-size' => $isLandscape ? [$height, $width] : [$width, $height], 'margin-left' => 0, 'margin-right' => 0, 'margin-top' => 0, 'margin-bottom' => 0]);
                $pdf->UseTemplate($template, 0, 0, $width, $height);
                $pdf->SetFont('dejavusans', '', 7.5);
                $pdf->SetTextColor(55, 65, 81);
                $textLeft = 45.0;
                $textWidth = max(60, $width - $textLeft - 15);
                $pdf->SetXY($textLeft, max(5, $height - 24));
                $pdf->MultiCell($textWidth, 4, self::NOTICE, 0, 'C');
                if ($pageCount > 1 && $page > 1 && filled($verificationUrl)) {
                    $safeUrl = htmlspecialchars($verificationUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $pdf->WriteFixedPosHTML(
                        '<barcode code="'.$safeUrl.'" type="QR" size="0.8" error="M" disableborder="1" />',
                        8.5,
                        max(5, $height - 38),
                        30,
                        30,
                        'auto'
                    );
                }
            }
            $pdf->Output($outputPath, Destination::FILE);
        } catch (\Throwable $exception) {
            @unlink($outputPath);
            throw new ElectronicSignatureException('Catatan legal BSrE gagal ditambahkan ke PDF: '.$exception->getMessage(), 0, $exception);
        }
        return $outputPath;
    }

    public function signatureAppearance(string $pdfPath): array
    {
        $tempDirectory = storage_path('app/temp-electronic-signatures');
        if (!is_dir($tempDirectory)) mkdir($tempDirectory, 0775, true);
        $pdf = new Mpdf(['tempDir' => $tempDirectory]);
        $pdf->SetSourceFile($pdfPath);
        $template = $pdf->ImportPage(1);
        $size = $pdf->getTemplateSize($template);
        return [
            'page' => 1,
            'xAxis' => self::BSRE_QR_POSITION_POINTS,
            'yAxis' => self::BSRE_QR_POSITION_POINTS,
            'width' => self::BSRE_QR_SIZE_POINTS,
            'height' => self::BSRE_QR_SIZE_POINTS,
        ];
    }

    private function pointsToMillimetres(float $points): float
    {
        return $points * self::MILLIMETRES_PER_INCH / self::POINTS_PER_INCH;
    }
}


