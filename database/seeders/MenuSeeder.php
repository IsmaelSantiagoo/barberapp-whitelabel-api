<?php

namespace Database\Seeders;

use App\Models\Menus;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Menus principais
        $menusData = [];

        // Inserir os dados
        foreach ($menusData as $menu) {
           Menus::create([
                'id' => $menu['id'],
                'titulo' => $menu['titulo'],
                'icone' => $menu['icone'],
                'rota' => $menu['rota'],
                'ordem' => $menu['ordem'],
                'menu_pai_id' => $menu['menu_pai_id'],
                'usuario_responsavel' => 1,
           ]);
        }
    }
}
