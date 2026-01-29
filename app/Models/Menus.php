<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menus extends Model
{
    protected $table = 'menus';

    // protected $primaryKey = 'id'; // padrão do Eloquent
    public $timestamps = true;

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'titulo',
        'icone',
        'rota',
        'ordem',
        'menu_pai_id',
        'usuario_responsavel',
    ];

    public function usuario()
    {
        return $this->belongsTo('App\\Models\\Usuarios', 'usuario_responsavel');
    }

    public function menu_pai()
    {
        return $this->belongsTo(Menus::class, 'menu_pai_id');
    }
}
