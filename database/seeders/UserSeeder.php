<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
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

        User::create([
            'name' => 'Desenvolvedor',
            'email' => 'dev@example.com',
            'profile_photo' => null,
            'password' => $defaultPassword,
            'first_access' => true,
            'role' => 'user'
        ]);
    }
}
