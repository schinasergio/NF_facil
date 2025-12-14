<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    protected $fillable = [
        'address_id',
        'razao_social',
        'nome_fantasia',
        'cpf_cnpj',
        'ie',
        'indicador_ie', // 1, 2, 9
        'email',
        'telefone',
        'ativo',
    ];

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}
