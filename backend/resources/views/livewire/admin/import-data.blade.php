<div>
    <x-header title="Import Data" subtitle="Run football-data imports without shell access" separator />

    <div class="max-w-3xl space-y-6">
        <x-card title="Run import" shadow>
            <div class="space-y-4">
                <p class="text-sm text-base-content/70">
                    These buttons run the import commands on the server from the browser.
                    They use the current environment automatically, so local runs stay local and Render runs use the Render environment.
                </p>

                <p class="text-xs text-base-content/50">
                    If prod imports fail with a database error after schema changes, run migrations first.
                </p>

                <div class="flex flex-wrap gap-3">
                    <x-button
                        label="Run migrations"
                        icon="o-wrench-screwdriver"
                        class="btn-outline"
                        wire:click="runMigrations"
                        wire:loading.attr="disabled"
                        wire:target="runMigrations"
                    />

                    <x-button
                        label="Rollback migrations"
                        icon="o-arrow-uturn-left"
                        class="btn-outline"
                        wire:click="runMigrationsRollback"
                        wire:loading.attr="disabled"
                        wire:target="runMigrationsRollback"
                    />

                    <x-button
                        label="Import teams"
                        icon="o-play"
                        class="btn-primary"
                        wire:click="runTeamsImport"
                        wire:loading.attr="disabled"
                        wire:target="runTeamsImport"
                    />

                    <x-button
                        label="Import games"
                        icon="o-play"
                        class="btn-primary"
                        wire:click="runGamesImport"
                        wire:loading.attr="disabled"
                        wire:target="runGamesImport"
                    />

                    <x-button
                        label="Import standings"
                        icon="o-play"
                        class="btn-primary"
                        wire:click="runStandingsImport"
                        wire:loading.attr="disabled"
                        wire:target="runStandingsImport"
                    />

                    <x-button
                        label="Import players"
                        icon="o-play"
                        class="btn-primary"
                        wire:click="runImport"
                        wire:loading.attr="disabled"
                        wire:target="runImport"
                    />
                </div>

                <x-input
                    label="Season"
                    wire:model="season"
                    type="number"
                    min="2000"
                    max="2100"
                    class="w-40"
                    hint="Used for games import"
                />

                @if ($running)
                    <div class="text-sm text-base-content/60 flex items-center gap-2">
                        <span class="loading loading-spinner loading-sm"></span>
                        Import running...
                    </div>
                @endif

                @if ($completed)
                    <x-alert title="{{ $message }}" class="alert-success" icon="o-check-circle" />
                @elseif ($failed)
                    <x-alert title="{{ $message }}" class="alert-error" icon="o-exclamation-triangle" />
                @endif
            </div>
        </x-card>

        @if ($output !== '')
            <x-card title="Command output" shadow>
                <pre class="bg-base-200 rounded-xl p-4 text-xs md:text-sm overflow-x-auto whitespace-pre-wrap">{{ $output }}</pre>
            </x-card>
        @endif
    </div>
</div>
