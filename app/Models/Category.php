<?php

namespace App\Models;

use App\Traits\BelongsToBarbershop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory, BelongsToBarbershop;

    protected $fillable = [
        'barbershop_id',
        'name',
        'active'
    ];

    /**
     * Uma categoria possui muitos serviços (ex: Cabelo -> Degradê, Social)
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
