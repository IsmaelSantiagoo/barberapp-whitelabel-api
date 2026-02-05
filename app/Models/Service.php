<?php

namespace App\Models;

use App\Traits\BelongsToBarbershop;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use BelongsToBarbershop;

    protected $fillable = [
        'barbershop_id',
        'name',
        'price',
        'duration_minutes',
        'description',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
        'price' => 'decimal:2',
    ];
}
