<?php

namespace Tests\Feature;

use App\Mail\NFeAuthorizedMail;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Nfe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MailTest extends TestCase
{
    use RefreshDatabase;

    public function test_nfe_authorized_mail_content()
    {
        // Setup content
        Storage::fake('local');

        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id, 'razao_social' => 'Emitente Ltd']);
        $customer = Customer::factory()->create(['company_id' => $company->id, 'razao_social' => 'Destinatario Ltd', 'email' => 'client@test.com']);

        // Mock XML file
        Storage::put('xmls/test_123.xml', '<nfe>XML Content</nfe>');

        $nfe = Nfe::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'numero' => 123,
            'serie' => 1,
            'status' => 'authorized',
            'valor_total' => 150.00,
            'xml_path' => 'xmls/test_123.xml',
            'chave' => '35230112345678000199550010000001231000000000',
            'protocolo' => '123456789'
        ]);

        // Create Mailable
        $mail = new NFeAuthorizedMail($nfe);

        // Assert Content
        $mail->assertSeeInHtml('Destinatario Ltd');
        $mail->assertSeeInHtml('Emitente Ltd');
        $mail->assertSeeInHtml('150,00');
        $mail->assertSeeInHtml('35230112345678000199550010000001231000000000');

        // Assert Attachment Logic
        // We verify the array length returned by attachments()
        // Note: PDF generation in attachment method calls DanfeService. 
        // We need to mock DanfeService or accept that it might fail/return empty if we don't mock it in a Unit test way.
        // However, this is a Feature test. NFeAuthorizedMail instantiates DanfeService via app().
        // Let's mock DanfeService to avoid Daexe/PDF binary complexity in this test.

        $this->mock(\App\Services\Fiscal\DanfeService::class, function ($mock) {
            $mock->shouldReceive('generatePdf')->andReturn('PDF_BINARY_CONTENT');
        });

        $attachments = $mail->attachments();
        $this->assertCount(2, $attachments); // XML + PDF

        // Verify XML attachment
        $this->assertEquals('NFe_35230112345678000199550010000001231000000000.xml', $attachments[0]->as);

        // Verify PDF attachment
        $this->assertEquals('DANFE_35230112345678000199550010000001231000000000.pdf', $attachments[1]->as);
    }
}
