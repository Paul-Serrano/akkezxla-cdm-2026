<div>
    <x-header title="User Management" subtitle="Create and manage users" separator />

    <div class="grid lg:grid-cols-2 gap-8 items-start">

        {{-- Create user form --}}
        <x-card title="Create user" shadow>
            @if ($created)
                <x-alert
                    title="{{ $createdName }} created successfully."
                    class="alert-success mb-4"
                    icon="o-check-circle"
                    x-data
                    x-init="setTimeout(() => $wire.set('created', false), 4000)"
                />
            @endif

            <x-form wire:submit="create">
                <x-input
                    label="Full name"
                    wire:model="name"
                    placeholder="Jane Doe"
                    icon="o-user"
                />
                <x-input
                    label="Alias"
                    wire:model="alias"
                    placeholder="janedoe"
                    icon="o-at-symbol"
                />
                <x-input
                    label="Email"
                    wire:model="email"
                    type="email"
                    placeholder="jane@example.com"
                    icon="o-envelope"
                />
                <x-input
                    label="Password"
                    wire:model="password"
                    type="password"
                    placeholder="Min. 8 characters"
                    icon="o-lock-closed"
                />
                <x-input
                    label="Confirm password"
                    wire:model="password_confirmation"
                    type="password"
                    placeholder="Repeat password"
                    icon="o-lock-closed"
                />

                <x-select
                    label="Role"
                    wire:model="role"
                    icon="o-shield-check"
                    :options="[
                        ['id' => 'regular',  'name' => 'Regular'],
                        ['id' => 'winamax',  'name' => 'Winamax'],
                        ['id' => 'admin',    'name' => 'Admin'],
                    ]"
                    option-value="id"
                    option-label="name"
                />

                <x-slot:actions>
                    <x-button label="Create user" type="submit" class="btn-primary" icon="o-user-plus" />
                </x-slot:actions>
            </x-form>
        </x-card>

        {{-- User list --}}
        <x-card title="All users" shadow>
            <x-table
                :headers="[
                    ['key' => 'name',  'label' => 'Name'],
                    ['key' => 'alias', 'label' => 'Alias'],
                    ['key' => 'role',  'label' => 'Role'],
                ]"
                :rows="$users"
            >
                @scope('cell_role', $user)
                    @php
                        $classes = match($user->role) {
                            'admin'   => 'badge-error',
                            'winamax' => 'badge-warning',
                            default   => 'badge-ghost',
                        };
                    @endphp
                    <x-badge :value="ucfirst($user->role)" class="{{ $classes }} badge-sm" />
                @endscope
            </x-table>
        </x-card>

    </div>
</div>
