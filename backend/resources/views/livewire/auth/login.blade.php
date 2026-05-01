<div class="flex items-center justify-center min-h-[60vh]">
    <x-card title="Login" shadow class="w-full max-w-sm">
        <x-form wire:submit="login">
            <x-input
                label="Email"
                wire:model="email"
                type="email"
                placeholder="you@example.com"
                icon="o-envelope"
                autofocus
            />
            <x-input
                label="Password"
                wire:model="password"
                type="password"
                placeholder="••••••••"
                icon="o-lock-closed"
            />

            @error('email')
                <x-alert title="{{ $message }}" class="alert-error text-sm" icon="o-x-circle" />
            @enderror

            <x-slot:actions>

                <x-button label="Login" type="submit" class="btn-primary" icon="o-arrow-right-on-rectangle" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
