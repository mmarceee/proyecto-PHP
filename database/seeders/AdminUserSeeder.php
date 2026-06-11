<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Admin;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear o actualizar el usuario base
        $user = User::updateOrCreate(
            ['email' => 'gendarapp@gmail.com'], // Condición de búsqueda
            [
                'name' => 'Admin',
                'apellido' => 'Sistema',
                'password' => Hash::make('Marceselaeat'),
                'telefono' => '000000000',
                'estado_usuario' => 'activo',
            ]
        );

        // 2. Registrar el usuario en la tabla admins
        Admin::firstOrCreate([
            'user_id' => $user->id
        ]);
    }
}