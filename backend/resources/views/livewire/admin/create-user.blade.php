<div>
    <x-header title="User Management" subtitle="Create and manage users" separator>
        <x-slot:actions>
            <x-button label="Manage Roles" icon="o-shield-check" href="{{ route('admin.roles') }}" class="btn-ghost btn-sm" />
        </x-slot:actions>
    </x-header>

    @if ($created)
        <x-alert
            title="{{ $createdName }} created successfully."
            class="alert-success mb-4"
            icon="o-check-circle"
            x-data
            x-init="setTimeout(() => $wire.set('created', false), 4000)"
        />
    @endif

    @if ($updated)
        <x-alert
            title="{{ $updatedName }} updated successfully."
            class="alert-success mb-4"
            icon="o-check-circle"
            x-data
            x-init="setTimeout(() => $wire.set('updated', false), 4000)"
        />
    @endif

    <div class="grid lg:grid-cols-2 gap-8 items-start">

        {{-- Create / Edit form --}}
        <x-card :title="$editing ? 'Edit user' : 'Create user'" shadow>
            @if ($editing)
                <x-form wire:submit="updateUser">
                    <x-input label="Full name"    wire:model="editName"  placeholder="Jane Doe"          icon="o-user" />
                    <x-input label="Alias"        wire:model="editAlias" placeholder="janedoe"           icon="o-at-symbol" />
                    <x-input label="Email"        wire:model="editEmail" type="email" placeholder="jane@example.com" icon="o-envelope" />

                    <div>
                        <label class="label"><span class="label-text font-medium">Roles</span></label>
                        <div class="flex flex-wrap gap-3 mt-1">
                            @foreach ($allRoles as $r)
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input
                                        type="checkbox"
                                        class="checkbox checkbox-sm"
                                        value="{{ $r->name }}"
                                        wire:model="editRoles"
                                    />
                                    <span class="text-sm">{{ $r->label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('editRoles') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                        @error('editRoles.*') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="divider text-xs text-base-content/40">Password reset (leave blank to keep current)</div>
                    <x-input label="New password"         wire:model="editPassword"              type="password" placeholder="Min. 8 characters" icon="o-lock-closed" />
                    <x-input label="Confirm new password" wire:model="editPassword_confirmation" type="password" placeholder="Repeat new password" icon="o-lock-closed" />

                    <x-slot:actions>
                        <x-button label="Cancel"       wire:click="cancelEdit" class="btn-ghost"   icon="o-x-mark" />
                        <x-button label="Save changes" type="submit"           class="btn-primary" icon="o-check" />
                    </x-slot:actions>
                </x-form>
            @else
                <x-form wire:submit="create">
                    <x-input label="Full name"       wire:model="name"                  placeholder="Jane Doe"          icon="o-user" />
                    <x-input label="Alias"           wire:model="alias"                 placeholder="janedoe"           icon="o-at-symbol" />
                    <x-input label="Email"           wire:model="email"                 type="email" placeholder="jane@example.com" icon="o-envelope" />
                    <x-input label="Password"        wire:model="password"              type="password" placeholder="Min. 8 characters" icon="o-lock-closed" />
                    <x-input label="Confirm password" wire:model="password_confirmation" type="password" placeholder="Repeat password" icon="o-lock-closed" />

                    <div>
                        <label class="label"><span class="label-text font-medium">Roles</span></label>
                        <div class="flex flex-wrap gap-3 mt-1">
                            @foreach ($allRoles as $r)
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input
                                        type="checkbox"
                                        class="checkbox checkbox-sm"
                                        value="{{ $r->name }}"
                                        wire:model="roles"
                                    />
                                    <span class="text-sm">{{ $r->label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('roles') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                        @error('roles.*') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <x-slot:actions>
                        <x-button label="Create user" type="submit" class="btn-primary" icon="o-user-plus" />
                    </x-slot:actions>
                </x-form>
            @endif
        </x-card>

        {{-- User list --}}
        <x-card title="All users" shadow>
            <x-table
                :headers="[
                    ['key' => 'name',    'label' => 'Name'],
                    ['key' => 'alias',   'label' => 'Alias'],
                    ['key' => 'roles',   'label' => 'Roles'],
                    ['key' => 'actions', 'label' => ''],
                ]"
                :rows="$users"
            >
                @scope('cell_roles', $user)
                    <div class="flex flex-wrap gap-1">
                        @foreach ($user->roles->sortBy('label') as $r)
                            @php
                                $cls = match($r->name) {
                                    'admin'   => 'badge-error',
                                    'winamax' => 'badge-warning',
                                    default   => 'badge-ghost',
                                };
                            @endphp
                            <x-badge :value="$r->label" class="{{ $cls }} badge-sm" />
                        @endforeach
                    </div>
                @endscope

                @scope('actions', $user)
                    <x-button
                        icon="o-pencil-square"
                        wire:click="startEdit({{ $user->id }})"
                        class="btn-ghost btn-sm"
                        tooltip="Edit"
                    />
                @endscope
            </x-table>
        </x-card>

    </div>
</div>
