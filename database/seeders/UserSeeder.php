<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@sandbox.com'],
            [
                'name'     => 'Sandbox Admin',
                'password' => Hash::make('password123'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'tester@sandbox.com'],
            [
                'name'     => 'Sandbox Tester',
                'password' => Hash::make('password123'),
            ]
        );

    }


}

