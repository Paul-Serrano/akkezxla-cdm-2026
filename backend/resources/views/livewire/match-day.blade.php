<div>
    {{-- Header with day navigation --}}
    <x-header separator>
        <x-slot:title>
            Match Day {{ $matchday }}
        </x-slot:title>
        <x-slot:subtitle>
            {{ $date ?? 'No games scheduled' }}
        </x-slot:subtitle>
        <x-slot:actions>
            <div class="join">
                <x-button
                    icon="o-chevron-left"
                    wire:click="$set('matchday', {{ max(1, $matchday - 1) }})"
                    :disabled="$matchday <= 1"
                    class="join-item btn-sm"
                    tooltip="Previous day"
                />
                <x-button
                    icon="o-chevron-right"
                    wire:click="$set('matchday', {{ min($totalDays, $matchday + 1) }})"
                    :disabled="$matchday >= $totalDays"
                    class="join-item btn-sm"
                    tooltip="Next day"
                />
            </div>
        </x-slot:actions>
    </x-header>

    @if ($games->isEmpty())
        <x-alert title="No games found for this day." icon="o-information-circle" class="alert-info" />
    @else
        {{-- MOBILE: stacked cards --}}
        <div class="block md:hidden">
            @foreach ($games as $game)
                <x-game :game="$game" />
                @if (!$loop->last)
                    <div class="flex items-center gap-3 my-1 px-2">
                        <div class="flex-1 border-t-2 border-base-300"></div>
                        <x-icon name="o-ellipsis-horizontal" class="w-4 h-4 text-base-300" />
                        <div class="flex-1 border-t-2 border-base-300"></div>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- DESKTOP: single column centered, wider cards --}}
        <div class="hidden md:flex flex-col gap-4 max-w-3xl mx-auto">
            @foreach ($games as $game)
                <x-game :game="$game" />
            @endforeach
        </div>
    @endif
</div>
