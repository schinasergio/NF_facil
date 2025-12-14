<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    protected $fillable = [
        'address_id',
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'ie',
        'im',
        'cnae',
        'regime_tributario',
        'email',
        'telefone',
        'status',
    ];

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}
