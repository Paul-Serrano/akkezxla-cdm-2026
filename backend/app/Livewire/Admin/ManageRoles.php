<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ManageRoles extends Component
{
    public string $name = '';
    public string $label = '';

    public bool $created = false;
    public string $createdLabel = '';

    /** Roles that cannot be deleted */
    protected array $protected = ['admin', 'winamax', 'regular'];

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->isAdmin(), 403);
    }

    public function create(): void
    {
        abort_unless(Auth::check() && Auth::user()->isAdmin(), 403);

        $this->validate([
            'name'  => ['required', 'string', 'max:32', 'unique:role,name', 'regex:/^[a-z][a-z0-9_]*$/'],
            'label' => ['required', 'string', 'max:64'],
        ]);

        $role = Role::create([
            'name'  => $this->name,
            'label' => $this->label,
        ]);

        $this->createdLabel = $role->label;
        $this->created = true;

        $this->reset(['name', 'label']);
    }

    public function delete(int $id): void
    {
        abort_unless(Auth::check() && Auth::user()->isAdmin(), 403);

        $role = Role::findOrFail($id);
        abort_if(in_array($role->name, $this->protected), 403);

        $role->delete();
    }

    public function render()
    {
        $roles = Role::orderBy('label')->get();

        return view('livewire.admin.manage-roles', compact('roles'));
    }
}
