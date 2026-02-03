<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Barbershop extends Model
{
    use HasUuids;

    protected $table = 'barbershops';

    protected $fillable = [
        'company_name',
        'slug',
        'domain',
        'primary_color',
        'logo_url',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'barbershop_id');
    }

    public function menus()
    {
        return $this->hasMany(Menu::class, 'barbershop_id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'barbershop_id');
    }

    public function businessHours()
    {
        return $this->hasMany(BusinessHour::class, 'barbershop_id');
    }

    /**
     * Cria os horários de funcionamento padrão para a barbearia
     * Segunda a Sexta: 09:00 - 18:00 (aberto)
     * Sábado: 09:00 - 13:00 (aberto)
     * Domingo: Fechado
     */
    public function createDefaultBusinessHours(): void
    {
        $defaultHours = [
            ['day_of_week' => 0, 'open_time' => '09:00:00', 'close_time' => '18:00:00', 'is_open' => false], // Domingo
            ['day_of_week' => 1, 'open_time' => '09:00:00', 'close_time' => '18:00:00', 'is_open' => true],  // Segunda
            ['day_of_week' => 2, 'open_time' => '09:00:00', 'close_time' => '18:00:00', 'is_open' => true],  // Terça
            ['day_of_week' => 3, 'open_time' => '09:00:00', 'close_time' => '18:00:00', 'is_open' => true],  // Quarta
            ['day_of_week' => 4, 'open_time' => '09:00:00', 'close_time' => '18:00:00', 'is_open' => true],  // Quinta
            ['day_of_week' => 5, 'open_time' => '09:00:00', 'close_time' => '18:00:00', 'is_open' => true],  // Sexta
            ['day_of_week' => 6, 'open_time' => '09:00:00', 'close_time' => '13:00:00', 'is_open' => true],  // Sábado
        ];

        foreach ($defaultHours as $hour) {
            $this->businessHours()->create($hour);
        }
    }
}
