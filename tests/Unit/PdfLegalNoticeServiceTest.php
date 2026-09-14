<?php

namespace Tests\Unit;

use App\Services\ElectronicSignature\PdfLegalNoticeService;
use Dompdf\Dompdf;
use Mpdf\Mpdf;
use Tests\TestCase;

class PdfLegalNoticeServiceTest extends TestCase
{
    public function test_it_stamps_an_existing_pdf_with_the_bsre_legal_notice(): void
    {
        $source = tempnam(sys_get_temp_dir(), 'tte-source-').'.pdf';
        $output = null;

        try {
            $dompdf = new Dompdf();
            $dompdf->loadHtml('<html><body><h1>Dokumen uji</h1></body></html>');
            $dompdf->render();
            file_put_contents($source, $dompdf->output());

            $output = app(PdfLegalNoticeService::class)->stamp($source);

            $this->assertFileExists($output);
            $this->assertStringStartsWith('%PDF-', file_get_contents($output));
            $this->assertGreaterThan(1000, filesize($output));
        } finally {
            if (is_file($source)) unlink($source);
            if ($output && is_file($output)) unlink($output);
        }
    }

    public function test_it_preserves_landscape_orientation_and_calculates_qr_position(): void
    {
        $source = tempnam(sys_get_temp_dir(), 'tte-landscape-').'.pdf';
        $output = null;
        try {
            $dompdf = new Dompdf();
            $dompdf->setPaper('a4', 'landscape');
            $dompdf->loadHtml('<html><body><h1>Sertifikat landscape</h1></body></html>');
            $dompdf->render();
            file_put_contents($source, $dompdf->output());

            $service = app(PdfLegalNoticeService::class);
            $output = $service->stamp($source);
            $appearance = $service->signatureAppearance($output);
            $this->assertSame(24, $appearance['xAxis']);
            $this->assertSame(85, $appearance['width']);
            $this->assertSame(24, $appearance['yAxis']);
        } finally {
            if (is_file($source)) unlink($source);
            if ($output && is_file($output)) unlink($output);
        }
    }

    public function test_it_adds_verification_qr_to_a_multi_page_document(): void
    {
        $source = tempnam(sys_get_temp_dir(), 'tte-pages-').'.pdf';
        $output = null;
        try {
            $dompdf = new Dompdf();
            $dompdf->loadHtml('<p>Halaman 1</p><div style="page-break-before:always">Halaman 2</div><div style="page-break-before:always">Halaman 3</div>');
            $dompdf->render();
            file_put_contents($source, $dompdf->output());

            $output = app(PdfLegalNoticeService::class)->stamp($source, 'https://integral.test/verifikasi-tte/contoh-token');
            $reader = new Mpdf(['tempDir' => storage_path('app/temp-electronic-signatures')]);

            $this->assertSame(3, $reader->SetSourceFile($output));
            $this->assertGreaterThan(1000, filesize($output));
        } finally {
            if (is_file($source)) unlink($source);
            if ($output && is_file($output)) unlink($output);
        }
    }
}

