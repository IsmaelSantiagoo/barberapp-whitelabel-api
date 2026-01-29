<?php

namespace Database\Seeders;

use App\Models\Usuarios;
use Illuminate\Database\Seeder;

class UsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = config('auth.default_sys_pass');

        if ($defaultPassword === null) {
            return;
        }

        Usuarios::create([
            'nome' => 'Desenvolvedor',
            'email' => 'dev@example.com',
            'foto_perfil' => null,
            'senha' => $defaultPassword,
            'status' => 1,
            'tipo' => 1
        ]);
    }
}
