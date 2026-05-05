<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /** @var array<string, array{email: string, name: string, alias: string, password: string, roles: string[]}> */
    private array $users = [
        [
            'email'    => 'paul.serrano08374@gmail.com',
            'name'     => 'Paul Serrano',
            'alias'    => 'Paulux',
            'password' => '08374Liverpool!',
            'roles'    => [User::ROLE_ADMIN, User::ROLE_AKKEZXLA],
        ],
        [
            'email'    => 'dorian.lorteight@hotmail.fr',
            'name'     => 'Dorian Lorteight',
            'alias'    => 'Dodo',
            'password' => '00000000',
            'roles'    => [User::ROLE_USPEG, User::ROLE_AKKEZXLA],
        ],
        [
            'email'    => 'ludo.lolo@gmail.com',
            'name'     => 'Ludo Lolo',
            'alias'    => 'Ludo',
            'password' => '00000000',
            'roles'    => [User::ROLE_REGULAR],
        ],
    ];

    public function run(): void
    {
        $roleCache = Role::all()->keyBy('name');

        foreach ($this->users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'alias'    => $data['alias'],
                    'password' => Hash::make($data['password']),
                ]
            );

            $roleIds = collect($data['roles'])
                ->map(fn ($name) => $roleCache->get($name)?->id)
                ->filter()
                ->values()
                ->all();

            $user->roles()->syncWithoutDetaching($roleIds);
        }
    }
}
