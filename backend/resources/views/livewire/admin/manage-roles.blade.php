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
                <div>
                    <label class="label"><span class="label-text text-sm font-medium">Color <span class="text-base-content/40 font-normal">(optional)</span></span></label>
                    <div class="flex items-center gap-2">
                        <input type="color" wire:model="color" class="w-10 h-10 rounded cursor-pointer border border-base-300" />
                        <input type="text" wire:model="color" placeholder="#3b82f6" class="input input-bordered input-sm w-32 font-mono" />
                    </div>
                </div>
                <x-slot:actions>
                    <x-button label="Create role" type="submit" class="btn-primary" icon="o-plus" />
                </x-slot:actions>
            </x-form>
        </x-card>

        {{-- Role list --}}
        <x-card title="All roles" shadow>
            <div class="overflow-x-auto">
                <table class="table table-sm w-full">
                    <thead>
                        <tr class="text-xs uppercase text-base-content/50">
                            <th>Label</th>
                            <th>Slug</th>
                            <th>Color</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr class="hover">
                                <td>
                                    <span
                                        class="badge badge-sm"
                                        @if($role->color) style="background-color: {{ $role->color }}; color: #fff; border-color: {{ $role->color }};" @endif
                                    >{{ $role->label }}</span>
                                </td>
                                <td><code class="text-xs text-base-content/60">{{ $role->name }}</code></td>
                                <td>
                                    @if ($editingId === $role->id)
                                        <div class="flex items-center gap-2">
                                            <input type="color" wire:model="editColor" class="w-8 h-8 rounded cursor-pointer border border-base-300" />
                                            <input type="text" wire:model="editColor" class="input input-bordered input-xs w-24 font-mono" />
                                            <x-button icon="o-check" wire:click="saveColor" class="btn-ghost btn-xs text-success" tooltip="Save" />
                                            <x-button icon="o-x-mark" wire:click="cancelEditColor" class="btn-ghost btn-xs" tooltip="Cancel" />
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            @if ($role->color)
                                                <span class="inline-block w-4 h-4 rounded-full border border-base-300" style="background-color: {{ $role->color }}"></span>
                                                <code class="text-xs text-base-content/60">{{ $role->color }}</code>
                                            @else
                                                <span class="text-xs text-base-content/30 italic">none</span>
                                            @endif
                                            <x-button icon="o-pencil-square" wire:click="startEditColor({{ $role->id }})" class="btn-ghost btn-xs opacity-50 hover:opacity-100" tooltip="Edit color" />
                                        </div>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if (!in_array($role->name, ['admin', 'akkezxla', 'uspeg', 'regular']))
                                        <x-button
                                            icon="o-trash"
                                            wire:click="delete({{ $role->id }})"
                                            wire:confirm="Delete the '{{ $role->label }}' role? Users assigned to it will keep the slug."
                                            class="btn-ghost btn-sm text-error"
                                            tooltip="Delete"
                                        />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

    </div>
</div>
