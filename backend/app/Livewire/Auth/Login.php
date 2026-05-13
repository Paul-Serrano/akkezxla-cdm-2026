<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Throttle;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';

    #[Throttle(5, 60)]
    public function login(): void
    {
        $this->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], true)) {
            $this->addError('email', 'Invalid credentials.');
            return;
        }

        session()->regenerate();
        $this->redirectIntended(default: '/matchday', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
