<?php

namespace App\Listeners;

use App\Events\NFeAuthorized;
use App\Mail\NFeAuthorizedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendNFeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(NFeAuthorized $event): void
    {
        $nfe = $event->nfe;

        if (!$nfe->customer || !$nfe->customer->email) {
            Log::warning("Skipping NFe email: Customer has no email address.", ['nfe_id' => $nfe->id]);
            return;
        }

        try {
            Log::info("Sending NFe email to customer.", ['nfe_id' => $nfe->id, 'email' => $nfe->customer->email]);

            Mail::to($nfe->customer->email)
                ->send(new NFeAuthorizedMail($nfe));

            Log::info("NFe email sent successfully.", ['nfe_id' => $nfe->id]);

        } catch (\Exception $e) {
            Log::error("Failed to send NFe email.", ['nfe_id' => $nfe->id, 'error' => $e->getMessage()]);
            // Depending on queue settings, this might retry. We let it fail for now.
            throw $e;
        }
    }
}
