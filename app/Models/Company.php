<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
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
        'ambiente', // 1=Produção, 2=Homologação
    ];

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function certificate(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Certificate::class);
    }
}
