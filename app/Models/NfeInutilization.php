<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NfeInutilization extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'serie',
        'numero_inicial',
        'numero_final',
        'justificativa',
        'protocolo',
        'status',
        'xml_path',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
