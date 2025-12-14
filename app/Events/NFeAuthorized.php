<?php

namespace App\Events;

use App\Models\Nfe;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NFeAuthorized
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $nfe;

    /**
     * Create a new event instance.
     */
    public function __construct(Nfe $nfe)
    {
        $this->nfe = $nfe;
    }
}
