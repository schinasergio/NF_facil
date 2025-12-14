<?php

namespace App\Mail;

use App\Models\Nfe;
use App\Services\Fiscal\DanfeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class NFeAuthorizedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nfe;
    protected $danfeService;

    /**
     * Create a new message instance.
     */
    public function __construct(Nfe $nfe)
    {
        $this->nfe = $nfe;
        // DanfeService instantiation might fail in constructor if DI isn't handled by Mailer
        // Better to generate PDFs before mailing or in the build step? 
        // For simplicity, we will instantiate or mock it here, actually better to just rely on DI in logic handling the mail sending
        // BUT Mailable serialization issues with services.
        // It's safer to generate the PDF *content* or *path* before creating the mail, OR utilize the service inside the build() method if possible, 
        // but serialization of service is bad.
        // We will assume the PDF is generated dynamically in attachments().
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nota Fiscal Eletrônica Autorizada - ' . $this->nfe->company->razao_social,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.nfe.authorized',
            with: [
                'nfe' => $this->nfe,
                'customerName' => $this->nfe->customer->razao_social,
                'companyName' => $this->nfe->company->razao_social,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // 1. XML Attachment
        if (Storage::exists($this->nfe->xml_path)) {
            $attachments[] = Attachment::fromStorage($this->nfe->xml_path)
                ->as("NFe_{$this->nfe->chave}.xml")
                ->withMime('application/xml');
        }

        // 2. PDF (DANFE) Attachment
        // Using DanfeService on the fly. Ideally, PDF should be saved to disk first.
        // For this phase, let's create a temporary PDF content via DanfeService.
        try {
            $danfeService = app(DanfeService::class);
            $pdfContent = $danfeService->generatePdf($this->nfe);

            $attachments[] = Attachment::fromData(fn() => $pdfContent, "DANFE_{$this->nfe->chave}.pdf")
                ->withMime('application/pdf');

        } catch (\Exception $e) {
            // Log error but send email without PDF if generation fails?
            // Or fail? Let's log and proceed.
            \Illuminate\Support\Facades\Log::error("Failed to attach DANFE to email: " . $e->getMessage());
        }

        return $attachments;
    }
}
