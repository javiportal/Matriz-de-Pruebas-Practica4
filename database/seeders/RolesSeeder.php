<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['name' => 'bibliotecario', 'guard_name' => 'web']);
        Role::create(['name' => 'estudiante', 'guard_name' => 'web']);
        Role::create(['name' => 'docente', 'guard_name' => 'web']);
    }
}