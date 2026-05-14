<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NfeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'nfe_id',
        'status',
        'message',
        'payload',
        'protocolo',
    ];

    public function nfe(): BelongsTo
    {
        return $this->belongsTo(Nfe::class, 'nfe_id');
    }
}
