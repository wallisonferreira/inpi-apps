<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $table = 'brands';

    public $fillable = [
        'marca_nome',
        'NCL',
        'numero_processo',
        'titulares',
        'status',
        'last_check',
        'cases_number',
    ];
}
