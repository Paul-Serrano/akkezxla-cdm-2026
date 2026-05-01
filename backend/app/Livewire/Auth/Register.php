<?php

namespace App\Livewire\Auth;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Register extends Component
{
    public string $name = '';
    public string $alias = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        $this->validate([
            'name'     => ['required', 'string', 'max:255'],
            'alias'    => ['required', 'string', 'max:255', 'unique:user,alias'],
            'email'    => ['required', 'email', 'unique:user,email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = \App\Models\User::create([
            'name'     => $this->name,
            'alias'    => $this->alias,
            'email'    => $this->email,
            'password' => Hash::make($this->password),
        ]);

        event(new Registered($user));
        Auth::login($user);
        $this->redirect('/matchday', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
