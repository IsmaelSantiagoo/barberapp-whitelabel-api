<?php

namespace App\Models;

use App\Traits\BelongsToBarbershop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Notification extends Model
{
    use BelongsToBarbershop, HasUuids;
    public $timestamps = false;

    protected $fillable = [
        'title',
        'message',
        'type',
        'link',
        'sent_at',
        'read_at',
        'user_id',
        'menu_id',
        'barbershop_id',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
