<?php

namespace App\Models;

use App\Traits\BelongsToBarbershop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasFactory, BelongsToBarbershop;

    protected $fillable = [
        'barbershop_id',
        'category_id',
        'name',
        'price',
        'duration_minutes',
        'description',
        'active'
    ];

    /**
     * O serviço pertence a uma categoria (ex: Degradê -> Cortes)
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
