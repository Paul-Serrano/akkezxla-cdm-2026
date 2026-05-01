<div>
    <x-header title="Configuration" subtitle="Points & betting rules" separator />

    <div class="max-w-lg">
        <x-card title="Scoring rules" shadow>
            <form wire:submit="save" class="flex flex-col gap-5">

                <x-input
                    :label="\App\Enums\ConfigKey::PointsSuperWin->label()"
                    wire:model="pointsSuperWin"
                    type="number" min="0" max="99"
                    :hint="\App\Enums\ConfigKey::PointsSuperWin->hint()"
                />

                <x-input
                    :label="\App\Enums\ConfigKey::PointsWin->label()"
                    wire:model="pointsWin"
                    type="number" min="0" max="99"
                    :hint="\App\Enums\ConfigKey::PointsWin->hint()"
                />

                <x-input
                    :label="\App\Enums\ConfigKey::PointsScorer->label()"
                    wire:model="pointsScorer"
                    type="number" min="0" max="99"
                    :hint="\App\Enums\ConfigKey::PointsScorer->hint()"
                />

                <x-input
                    :label="\App\Enums\ConfigKey::TotalPlayerBet->label()"
                    wire:model="totalPlayerBet"
                    type="number" min="1" max="99"
                    :hint="\App\Enums\ConfigKey::TotalPlayerBet->hint()"
                />

                <div class="flex items-center gap-3">
                    <x-button label="Save" icon="o-check" type="submit" class="btn-primary" />

                    @if ($saved)
                        <span
                            class="text-success text-sm flex items-center gap-1"
                            x-data
                            x-init="setTimeout(() => $wire.set('saved', false), 2000)"
                        >
                            <x-icon name="o-check-circle" class="w-4 h-4" />
                            Saved
                        </span>
                    @endif
                </div>

                @error('pointsSuperWin') <p class="text-error text-sm">{{ $message }}</p> @enderror
                @error('pointsWin')      <p class="text-error text-sm">{{ $message }}</p> @enderror
                @error('pointsScorer')   <p class="text-error text-sm">{{ $message }}</p> @enderror
                @error('totalPlayerBet') <p class="text-error text-sm">{{ $message }}</p> @enderror

            </form>
        </x-card>
    </div>
</div>
