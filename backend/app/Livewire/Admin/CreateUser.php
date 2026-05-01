<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CreateUser extends Component
{
    public string $name = '';
    public string $alias = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = User::ROLE_REGULAR;

    public bool $created = false;
    public string $createdName = '';

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->isAdmin(), 403);
    }

    public function create(): void
    {
        abort_unless(Auth::check() && Auth::user()->isAdmin(), 403);

        $this->validate([
            'name'     => ['required', 'string', 'max:255'],
            'alias'    => ['required', 'string', 'max:255', 'unique:user,alias'],
            'email'    => ['required', 'email', 'unique:user,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'role'     => ['required', 'in:admin,winamax,regular'],
        ]);

        $user = User::create([
            'name'     => $this->name,
            'alias'    => $this->alias,
            'email'    => $this->email,
            'password' => Hash::make($this->password),
            'role'     => $this->role,
        ]);

        $this->createdName = $user->name;
        $this->created = true;

        $this->reset(['name', 'alias', 'email', 'password', 'password_confirmation', 'role']);
        $this->role = User::ROLE_REGULAR;
    }

    public function render()
    {
        $users = User::orderBy('name')->get(['id', 'name', 'alias', 'email', 'role']);

        return view('livewire.admin.create-user', compact('users'));
    }
}
