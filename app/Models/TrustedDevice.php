<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrustedDevice extends Model
{
  public $timestamps = false;

  protected $table = 'trusted_devices';

  protected $fillable = [
    'user_id',
    'device_token_hash',
    'barbershop_id',
    'expires_at',
    'last_used_at',
  ];

  protected $casts = [
    'expires_at' => 'datetime',
    'last_used_at' => 'datetime',
    'created_at' => 'datetime',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function barbershop()
  {
    return $this->belongsTo(Barbershop::class);
  }

  public function isExpired(): bool
  {
    return $this->expires_at->isPast();
  }

  public function scopeValidToken($query, string $tokenHash, string $barbershopId)
  {
    return $query->where('device_token_hash', $tokenHash)
      ->where('barbershop_id', $barbershopId)
      ->where('expires_at', '>', now());
  }
}
