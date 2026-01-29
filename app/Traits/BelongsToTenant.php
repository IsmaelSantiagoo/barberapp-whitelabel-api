<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    // O "boot" da trait é rodado automaticamente pelo Laravel
    protected static function bootBelongsToTenant()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (session()->has('tenant_id')) {
                // Pegamos o nome da tabela dinamicamente
                $table = $builder->getModel()->getTable();

                // Qualificamos a coluna: "menus.tenant_id" em vez de apenas "tenant_id"
                $builder->where($table . '.tenant_id', session()->get('tenant_id'));
            }
        });

        static::creating(function ($model) {
            if (session()->has('tenant_id')) {
                $model->tenant_id = session()->get('tenant_id');
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
