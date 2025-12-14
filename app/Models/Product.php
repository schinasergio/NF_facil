<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
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
