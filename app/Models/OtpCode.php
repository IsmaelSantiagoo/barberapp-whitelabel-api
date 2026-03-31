<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    public $timestamps = false;

    protected $table = 'otp_codes';

    protected $fillable = [
        'phone',
        'code',
        'barbershop_id',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function barbershop()
    {
        return $this->belongsTo(Barbershop::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function scopeValid($query, string $phone, string $code, string $barbershopId)
    {
        return $query->where('phone', $phone)
            ->where('code', $code)
            ->where('barbershop_id', $barbershopId)
            ->where('expires_at', '>', now())
            ->whereNull('verified_at');
    }

    public function scopeForPhone($query, string $phone, string $barbershopId)
    {
        return $query->where('phone', $phone)
            ->where('barbershop_id', $barbershopId);
    }
}
