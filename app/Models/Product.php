<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'nome',
        'codigo_sku',
        'ncm',
        'cest',
        'unidade',
        'preco_venda',
        'origem',
        'ativo',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
