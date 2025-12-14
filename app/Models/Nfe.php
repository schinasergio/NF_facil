<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nfe extends Model
{
    protected $table = 'nves'; // Ensuring correct table name

    protected $fillable = [
        'company_id',
        'customer_id',
        'numero',
        'serie',
        'chave',
        'xml_path',
        'status',
        'valor_total',
        'protocolo',
        'mensagem_sefaz',
        'data_recebimento',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
