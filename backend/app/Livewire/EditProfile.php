<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class EditProfile extends Component
{
    public string $name     = '';
    public string $alias    = '';
    public string $email    = '';

    public string $currentPassword    = '';
    public string $newPassword        = '';
    public string $newPasswordConfirm = '';

    public bool $savedProfile  = false;
    public bool $savedPassword = false;

    public function mount(): void
    {
        abort_unless(Auth::check(), 403);

        $user        = Auth::user();
        $this->name  = $user->name;
        $this->alias = $user->alias;
        $this->email = $user->email;
    }

    public function saveProfile(): void
    {
        $user = Auth::user();

        $this->validate([
            'name'  => ['required', 'string', 'max:255'],
            'alias' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('user', 'email')->ignore($user->id)],
        ]);

        $user->update([
            'name'  => $this->name,
            'alias' => $this->alias,
            'email' => $this->email,
        ]);

        $this->savedProfile = true;
    }

    public function savePassword(): void
    {
        $this->validate([
            'currentPassword'    => ['required'],
            'newPassword'        => ['required', 'min:8', 'same:newPasswordConfirm'],
            'newPasswordConfirm' => ['required'],
        ]);

        $user = Auth::user();

        if (! Hash::check($this->currentPassword, $user->password)) {
            $this->addError('currentPassword', 'Current password is incorrect.');
            return;
        }

        $user->update(['password' => $this->newPassword]);

        $this->currentPassword    = '';
        $this->newPassword        = '';
        $this->newPasswordConfirm = '';
        $this->savedPassword      = true;
    }

    public function render()
    {
        return view('livewire.edit-profile');
    }
}
