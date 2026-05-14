<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NfeNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'ambiente',
        'serie',
        'numero',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
