<?php

namespace App\Traits;

use App\Models\Barbershop;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToBarbershop
{
    // O "boot" da trait é rodado automaticamente pelo Laravel
    protected static function bootBelongsToBarbershop()
    {
        static::addGlobalScope('barbershop', function (Builder $builder) {
            if (session()->has('barbershop_id')) {
                // Pegamos o nome da tabela dinamicamente
                $table = $builder->getModel()->getTable();

                // Qualificamos a coluna: "menus.barbershop_id" em vez de apenas "barbershop_id"
                $builder->where($table . '.barbershop_id', session()->get('barbershop_id'));
            }
        });

        static::creating(function ($model) {
            if (session()->has('barbershop_id')) {
                $model->barbershop_id = session()->get('barbershop_id');
            }
        });
    }

    public function barbershop()
    {
        return $this->belongsTo(Barbershop::class);
    }
}
