<?php

namespace Database\Seeders;

use App\Models\Menu;
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
           Menu::create([
                'id' => $menu['id'],
                'title' => $menu['title'],
                'icon' => $menu['icon'],
                'route' => $menu['route'],
                'order' => $menu['order'],
                'parent_menu_id' => $menu['parent_menu_id'],
                'responsible_user' => 1,
           ]);
        }
    }
}
