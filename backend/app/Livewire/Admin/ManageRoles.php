<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ManageRoles extends Component
{
    public string $name  = '';
    public string $label = '';
    public string $color = '';

    public bool $created = false;
    public string $createdLabel = '';
    public bool $deleted = false;
    public string $deletedLabel = '';
    public string $deleteError = '';

    public ?int $editingId    = null;
    public string $editColor  = '';

    /** Roles that cannot be deleted */
    protected array $protected = ['admin', 'akkezxla', 'uspeg', 'regular'];

    public function mount(): void
    {
    }

    public function create(): void
    {
        $this->validate([
            'name'  => ['required', 'string', 'max:32', 'unique:role,name', 'regex:/^[a-z][a-z0-9_]*$/'],
            'label' => ['required', 'string', 'max:64'],
            'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $role = Role::create([
            'name'  => $this->name,
            'label' => $this->label,
            'color' => $this->color ?: null,
        ]);

        $this->createdLabel = $role->label;
        $this->created = true;

        $this->reset(['name', 'label', 'color']);
    }

    public function startEditColor(int $id): void
    {
        $role = Role::findOrFail($id);
        $this->editingId = $id;
        $this->editColor = $role->color ?? '';
    }

    public function saveColor(): void
    {
        $this->validate(['editColor' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/']]);

        Role::findOrFail($this->editingId)->update(['color' => $this->editColor ?: null]);

        $this->editingId = null;
        $this->editColor = '';
    }

    public function cancelEditColor(): void
    {
        $this->editingId = null;
        $this->editColor = '';
    }

    public function deleteRole(int $id): void
    {
        $this->deleteError = '';

        $role = Role::findOrFail($id);
        if (in_array($role->name, $this->protected, true)) {
            $this->deleteError = 'This system role cannot be deleted.';
            return;
        }

        $label = $role->label;
        $role->delete();

        $this->deletedLabel = $label;
        $this->deleted = true;
        $this->created = false;
    }

    public function render()
    {
        $roles = Role::orderBy('label')->get();

        return view('livewire.admin.manage-roles', [
            'roles'     => $roles,
            'editingId' => $this->editingId,
        ]);
    }
}
