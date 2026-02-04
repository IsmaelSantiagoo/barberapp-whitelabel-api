<?php

namespace App\Models;

use App\Traits\BelongsToBarbershop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use BelongsToBarbershop;

    protected $fillable = [
        'customer_id',
        'service_id',
        'date',
        'time',
        'status',
        'notes'
    ];

    /**
     * A agenda pode ter um cliente associado
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }

    /**
     * A agenda pode ter um serviço associado
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    /**
     * A agenda pertence a uma barbearia
     */
    public function barbershop(): BelongsTo
    {
        return $this->belongsTo(Barbershop::class, 'barbershop_id', 'id');
    }
}
