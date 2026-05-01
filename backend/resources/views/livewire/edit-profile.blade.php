<div>
    <x-header title="My Profile" subtitle="Update your account information" separator />

    <div class="max-w-lg flex flex-col gap-6">

        {{-- Identity --}}
        <x-card title="Identity" shadow>
            <form wire:submit="saveProfile" class="flex flex-col gap-5">

                <x-input
                    label="Name"
                    wire:model="name"
                    placeholder="Your full name"
                    icon="o-user"
                />

                <x-input
                    label="Alias"
                    wire:model="alias"
                    placeholder="Your display name"
                    icon="o-identification"
                />

                <x-input
                    label="Email"
                    wire:model="email"
                    type="email"
                    placeholder="your@email.com"
                    icon="o-envelope"
                />

                <div class="flex items-center gap-3">
                    <x-button label="Save" icon="o-check" type="submit" class="btn-primary" />

                    @if ($savedProfile)
                        <span
                            class="text-success text-sm flex items-center gap-1"
                            x-data
                            x-init="setTimeout(() => $wire.set('savedProfile', false), 2000)"
                        >
                            <x-icon name="o-check-circle" class="w-4 h-4" />
                            Saved
                        </span>
                    @endif
                </div>

                @error('name')  <p class="text-error text-sm">{{ $message }}</p> @enderror
                @error('alias') <p class="text-error text-sm">{{ $message }}</p> @enderror
                @error('email') <p class="text-error text-sm">{{ $message }}</p> @enderror

            </form>
        </x-card>

        {{-- Password --}}
        <x-card title="Change password" shadow>
            <form wire:submit="savePassword" class="flex flex-col gap-5">

                <x-input
                    label="Current password"
                    wire:model="currentPassword"
                    type="password"
                    icon="o-lock-closed"
                />

                <x-input
                    label="New password"
                    wire:model="newPassword"
                    type="password"
                    icon="o-lock-open"
                />

                <x-input
                    label="Confirm new password"
                    wire:model="newPasswordConfirm"
                    type="password"
                    icon="o-lock-open"
                />

                <div class="flex items-center gap-3">
                    <x-button label="Change password" icon="o-check" type="submit" class="btn-primary" />

                    @if ($savedPassword)
                        <span
                            class="text-success text-sm flex items-center gap-1"
                            x-data
                            x-init="setTimeout(() => $wire.set('savedPassword', false), 2000)"
                        >
                            <x-icon name="o-check-circle" class="w-4 h-4" />
                            Password updated
                        </span>
                    @endif
                </div>

                @error('currentPassword')    <p class="text-error text-sm">{{ $message }}</p> @enderror
                @error('newPassword')        <p class="text-error text-sm">{{ $message }}</p> @enderror
                @error('newPasswordConfirm') <p class="text-error text-sm">{{ $message }}</p> @enderror

            </form>
        </x-card>

    </div>
</div>
