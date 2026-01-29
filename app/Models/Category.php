<?php

namespace App\Models;

use App\Traits\BelongsToTenant; // Importante: a trait que criamos
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
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
