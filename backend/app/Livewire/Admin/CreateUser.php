<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CreateUser extends Component
{
    // ── Create form ────────────────────────────────────────────────────────────
    public string $name = '';
    public string $alias = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    /** @var string[] */
    public array $roles = [User::ROLE_REGULAR];

    public bool $created = false;
    public string $createdName = '';

    // ── Edit form ──────────────────────────────────────────────────────────────
    public bool $editing = false;
    public ?int $editingId = null;

    public string $editName = '';
    public string $editAlias = '';
    public string $editEmail = '';
    /** @var string[] */
    public array $editRoles = [];
    public string $editPassword = '';
    public string $editPassword_confirmation = '';

    public bool $updated = false;
    public string $updatedName = '';

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->isAdmin(), 403);
    }

    // ── Create ─────────────────────────────────────────────────────────────────
    public function create(): void
    {
        abort_unless(Auth::check() && Auth::user()->isAdmin(), 403);

        $this->validate([
            'name'     => ['required', 'string', 'max:255'],
            'alias'    => ['required', 'string', 'max:255', 'unique:user,alias'],
            'email'    => ['required', 'email', 'unique:user,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'roles'    => ['required', 'array', 'min:1'],
            'roles.*'  => [Rule::exists('role', 'name')],
        ]);

        $user = User::create([
            'name'     => $this->name,
            'alias'    => $this->alias,
            'email'    => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $roleIds = Role::whereIn('name', $this->roles)->pluck('id');
        $user->roles()->sync($roleIds);

        $this->createdName = $user->name;
        $this->created = true;

        $this->reset(['name', 'alias', 'email', 'password', 'password_confirmation']);
        $this->roles = [User::ROLE_REGULAR];
    }

    // ── Edit ───────────────────────────────────────────────────────────────────
    public function startEdit(int $userId): void
    {
        $user = User::with('roles')->findOrFail($userId);

        $this->editingId    = $userId;
        $this->editName     = $user->name;
        $this->editAlias    = $user->alias;
        $this->editEmail    = $user->email;
        $this->editRoles    = $user->roles->pluck('name')->toArray();
        $this->editPassword = '';
        $this->editPassword_confirmation = '';
        $this->editing = true;
        $this->resetValidation();
    }

    public function cancelEdit(): void
    {
        $this->editing   = false;
        $this->editingId = null;
        $this->reset(['editName', 'editAlias', 'editEmail', 'editRoles', 'editPassword', 'editPassword_confirmation']);
        $this->resetValidation();
    }

    public function updateUser(): void
    {
        abort_unless(Auth::check() && Auth::user()->isAdmin(), 403);

        $rules = [
            'editName'  => ['required', 'string', 'max:255'],
            'editAlias' => ['required', 'string', 'max:255', Rule::unique('user', 'alias')->ignore($this->editingId)],
            'editEmail' => ['required', 'email', Rule::unique('user', 'email')->ignore($this->editingId)],
            'editRoles'   => ['required', 'array', 'min:1'],
            'editRoles.*' => [Rule::exists('role', 'name')],
        ];

        if ($this->editPassword !== '') {
            $rules['editPassword'] = ['required', 'min:8', 'confirmed'];
        }

        $this->validate($rules);

        $user = User::findOrFail($this->editingId);

        $data = [
            'name'  => $this->editName,
            'alias' => $this->editAlias,
            'email' => $this->editEmail,
        ];

        if ($this->editPassword !== '') {
            $data['password'] = Hash::make($this->editPassword);
        }

        $user->update($data);

        $roleIds = Role::whereIn('name', $this->editRoles)->pluck('id');
        $user->roles()->sync($roleIds);

        $this->updatedName = $user->name;
        $this->updated = true;
        $this->cancelEdit();
    }

    public function render()
    {
        $users = User::with('roles')->orderBy('name')->get(['id', 'name', 'alias', 'email']);
        $allRoles = Role::orderBy('label')->get(['name', 'label']);

        return view('livewire.admin.create-user', compact('users', 'allRoles'));
    }
}
