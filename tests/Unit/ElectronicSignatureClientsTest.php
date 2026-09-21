<?php

namespace Tests\Unit;

use App\Services\ElectronicSignature\BsreClient;
use App\Services\ElectronicSignature\ElectronicSignatureException;
use App\Services\ElectronicSignature\JctClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ElectronicSignatureClientsTest extends TestCase
{
    public function test_bsre_client_returns_signed_pdf_without_persisting_passphrase(): void
    {
        config()->set('services.bsre', ['url'=>'https://bsre.test/sign','username'=>'client','password'=>'secret','timeout'=>30,'verify_ssl'=>true]);
        Http::fake(['https://bsre.test/*'=>Http::response('%PDF-signed-content',200,['Content-Type'=>'application/pdf'])]);
        $path=tempnam(sys_get_temp_dir(),'bsre-test-');file_put_contents($path,'%PDF-source');
        try {
            $result=app(BsreClient::class)->signInvisible($path,'1234567890','temporary-passphrase');
            $this->assertStringStartsWith('%PDF-',$result);
            Http::assertSentCount(1);
        } finally {@unlink($path);}
    }

    public function test_bsre_client_converts_json_failure_to_safe_exception(): void
    {
        config()->set('services.bsre', ['url'=>'https://bsre.test/sign','username'=>'client','password'=>'secret','timeout'=>30,'verify_ssl'=>true]);
        Http::fake(['https://bsre.test/*'=>Http::response(['status_code'=>400,'error'=>'Passphrase tidak sesuai'],400)]);
        $path=tempnam(sys_get_temp_dir(),'bsre-test-');file_put_contents($path,'%PDF-source');
        try {
            $this->expectException(ElectronicSignatureException::class);
            app(BsreClient::class)->signInvisible($path,'1234567890','wrong-passphrase');
        } finally {@unlink($path);}
    }

    public function test_bsre_connection_failure_has_a_clear_message(): void
    {
        config()->set('services.bsre', ['url'=>'https://bsre.test/sign','username'=>'client','password'=>'secret','timeout'=>30,'verify_ssl'=>true]);
        Http::fake(['https://bsre.test/*'=>Http::failedConnection()]);
        $path=tempnam(sys_get_temp_dir(),'bsre-test-');file_put_contents($path,'%PDF-source');
        try {
            $this->expectException(ElectronicSignatureException::class);
            $this->expectExceptionMessage('Server BSrE tidak dapat dihubungi');
            app(BsreClient::class)->signInvisible($path,'1234567890','temporary-passphrase');
        } finally {@unlink($path);}
    }

    public function test_bsre_visible_signature_accepts_verification_qr_link(): void
    {
        config()->set('services.bsre', ['url'=>'https://bsre.test/sign','username'=>'client','password'=>'secret','timeout'=>30,'verify_ssl'=>true]);
        Http::fake(['https://bsre.test/*'=>Http::response('%PDF-signed-with-qr',200)]);
        $path=tempnam(sys_get_temp_dir(),'bsre-test-');file_put_contents($path,'%PDF-source');
        try {
            $result=app(BsreClient::class)->signVisible($path,'1234567890','temporary-passphrase',[
                'page'=>1,'xAxis'=>430,'yAxis'=>25,'width'=>120,'height'=>120,
                'image'=>'false','linkQR'=>'https://integral.test/verifikasi-tte/token',
            ]);
            $this->assertStringStartsWith('%PDF-',$result);
        } finally {@unlink($path);}
    }

    public function test_jct_client_accepts_nested_template_payload(): void
    {
        config()->set('services.jct', ['url'=>'https://jct.test','username'=>null,'password'=>null,'timeout'=>30]);
        Http::fake([
            'https://jct.test/getTemplateForIntegral'=>Http::response(['data'=>['data'=>[['id_template'=>'T-NESTED']]]]),
        ]);

        $this->assertSame('T-NESTED', app(JctClient::class)->templates()[0]['id_template']);
    }

    public function test_jct_client_reads_templates_and_validates_downloaded_pdf(): void
    {
        config()->set('services.jct', ['url'=>'https://jct.test','username'=>null,'password'=>null,'timeout'=>30]);
        Http::fake([
            'https://jct.test/getTemplateForIntegral'=>Http::response(['data'=>[['id_template'=>'T-1']]]),
            'https://jct.test/downloadforintegral/*'=>Http::response('%PDF-certificate',200,['Content-Type'=>'application/pdf']),
        ]);
        $this->assertSame('T-1',app(JctClient::class)->templates()[0]['id_template']);
        $this->assertStringStartsWith('%PDF-',app(JctClient::class)->downloadCertificate('activity','user'));
    }
}
