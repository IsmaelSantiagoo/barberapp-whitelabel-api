<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use BelongsToTenant;
    protected $table = 'menus';

    // protected $primaryKey = 'id'; // padrão do Eloquent
    public $timestamps = true;

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'title',
        'icon',
        'route',
        'order',
        'parent_menu_id',
        'responsible_user',
    ];

    public function user()
    {
        return $this->belongsTo('App\\Models\\User', 'responsible_user');
    }

    public function parent_menu()
    {
        return $this->belongsTo(Menu::class, 'parent_menu_id');
    }
}
