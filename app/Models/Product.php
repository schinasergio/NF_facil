<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'nome',
        'codigo_sku',
        'ncm',
        'cest',
        'unidade',
        'preco_venda',
        'origem',
        'ativo',
    ];
}
