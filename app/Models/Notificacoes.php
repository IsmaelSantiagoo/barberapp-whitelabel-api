<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacoes extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'titulo',
        'mensagem',
        'tipo',
        'link',
        'data_envio',
        'data_leitura',
        'usuario_id',
        'menu_id',
    ];

    protected $casts = [
        'data_envio' => 'datetime',
        'data_leitura' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuarios::class, 'usuario_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menus::class, 'menu_id');
    }
}
