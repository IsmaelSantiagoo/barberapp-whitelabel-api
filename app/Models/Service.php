<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
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
