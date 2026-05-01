<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'paul.serrano08374@gmail.com'],
            [
                'name'     => 'Paul Serrano',
                'alias'    => 'Paulux',
                'password' => Hash::make('08374Liverpool!'),
                'role'     => User::ROLE_ADMIN,
            ]
        );
    }
}
