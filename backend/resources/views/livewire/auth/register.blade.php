<div class="flex items-center justify-center min-h-[60vh]">
    <x-card title="Create account" shadow class="w-full max-w-sm">
        <x-form wire:submit="register">
            <x-input
                label="Name"
                wire:model="name"
                placeholder="Your full name"
                icon="o-user"
                autofocus
            />
            <x-input
                label="Alias"
                wire:model="alias"
                placeholder="Your display name"
                icon="o-at-symbol"
            />
            <x-input
                label="Email"
                wire:model="email"
                type="email"
                placeholder="you@example.com"
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

            <x-slot:actions>
                <a href="{{ route('login') }}" class="link link-primary text-sm">Already registered?</a>
                <x-button label="Register" type="submit" class="btn-primary" icon="o-user-plus" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
