<div>
    <x-header title="Role Management" subtitle="Create and manage roles" separator>
        <x-slot:actions>
            <x-button label="Users" icon="o-users" href="{{ route('admin.users') }}" class="btn-ghost btn-sm" />
        </x-slot:actions>
    </x-header>

    @if ($created)
        <x-alert
            title="{{ $createdLabel }} role created successfully."
            class="alert-success mb-4"
            icon="o-check-circle"
            x-data
            x-init="setTimeout(() => $wire.set('created', false), 4000)"
        />
    @endif

    <div class="grid lg:grid-cols-2 gap-8 items-start">

        {{-- Create role form --}}
        <x-card title="Create role" shadow>
            <x-form wire:submit="create">
                <x-input
                    label="Slug"
                    wire:model="name"
                    placeholder="uspeg"
                    icon="o-tag"
                    hint="Lowercase letters, digits and underscores only."
                />
                <x-input
                    label="Label"
                    wire:model="label"
                    placeholder="USPEG"
                    icon="o-pencil"
                />
                <x-slot:actions>
                    <x-button label="Create role" type="submit" class="btn-primary" icon="o-plus" />
                </x-slot:actions>
            </x-form>
        </x-card>

        {{-- Role list --}}
        <x-card title="All roles" shadow>
            <x-table
                :headers="[
                    ['key' => 'label',   'label' => 'Label'],
                    ['key' => 'name',    'label' => 'Slug'],
                    ['key' => 'actions', 'label' => ''],
                ]"
                :rows="$roles"
            >
                @scope('cell_name', $role)
                    <code class="text-xs text-base-content/60">{{ $role->name }}</code>
                @endscope

                @scope('actions', $role)
                    @if (!in_array($role->name, ['admin', 'winamax', 'regular']))
                        <x-button
                            icon="o-trash"
                            wire:click="delete({{ $role->id }})"
                            wire:confirm="Delete the '{{ $role->label }}' role? Users assigned to it will keep the slug."
                            class="btn-ghost btn-sm text-error"
                            tooltip="Delete"
                        />
                    @endif
                @endscope
            </x-table>
        </x-card>

    </div>
</div>
