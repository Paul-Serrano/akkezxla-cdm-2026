@props(['game'])

@php
    use App\Enums\GameStatus;
    $home       = $game->homeTeam;
    $away       = $game->awayTeam;
    $gameStatus = GameStatus::fromGame($game);
    $played     = $gameStatus === GameStatus::Ended;
    $date       = \Carbon\Carbon::parse($game->startDate)->format('d M · H:i');
@endphp

{{-- MOBILE --}}
<div class="block md:hidden">
    <div class="card card-compact bg-base-100 shadow border border-base-200 w-full">
        <div class="card-body">
            <div class="flex items-center justify-center gap-2 mb-1">
                <span class="text-xs text-base-content/50">{{ $date }}</span>
                <span class="badge badge-xs {{ $gameStatus->badgeClass() }}">{{ $gameStatus->label() }}</span>
            </div>
            <div class="flex items-center justify-between gap-2">
                {{-- Home --}}
                <div class="flex flex-col items-center gap-1 flex-1">
                    <img src="{{ $home->crest }}" alt="{{ $home->shortName }}" class="w-10 h-10 object-contain" />
                    <span class="text-xs font-medium text-center leading-tight">{{ $home->shortName }}</span>
                </div>
                {{-- Score --}}
                <div class="flex items-center gap-1">
                    @if ($played)
                        <span class="text-2xl font-black tabular-nums">{{ $game->scoreHome }}</span>
                        <span class="text-base-content/40 font-light text-xl">—</span>
                        <span class="text-2xl font-black tabular-nums">{{ $game->scoreAway }}</span>
                    @else
                        <span class="text-lg font-semibold text-base-content/40">vs</span>
                    @endif
                </div>
                {{-- Away --}}
                <div class="flex flex-col items-center gap-1 flex-1">
                    <img src="{{ $away->crest }}" alt="{{ $away->shortName }}" class="w-10 h-10 object-contain" />
                    <span class="text-xs font-medium text-center leading-tight">{{ $away->shortName }}</span>
                </div>
            </div>
            <div class="divider my-0"></div>
            <livewire:place-bet :game="$game" :key="'mob-bet-'.$game->id" />
        </div>
    </div>
</div>

{{-- DESKTOP --}}
<div class="hidden md:block">
    <div class="card bg-base-100 shadow border border-base-200 w-full">
        <div class="card-body flex-row items-center gap-4 py-4 px-6">
            {{-- Home --}}
            <div class="flex items-center gap-3 flex-1 justify-end">
                <span class="font-semibold text-right">{{ $home->name }}</span>
                <img src="{{ $home->crest }}" alt="{{ $home->shortName }}" class="w-10 h-10 object-contain" />
            </div>
            {{-- Score / VS --}}
            <div class="flex items-center gap-3 shrink-0 min-w-[90px] justify-center">
                @if ($played)
                    <span class="text-3xl font-black tabular-nums">{{ $game->scoreHome }}</span>
                    <span class="text-base-content/30 font-light">—</span>
                    <span class="text-3xl font-black tabular-nums">{{ $game->scoreAway }}</span>
                @else
                    <div class="flex flex-col items-center gap-0.5">
                        <span class="badge badge-xs {{ $gameStatus->badgeClass() }}">{{ $gameStatus->label() }}</span>
                        <span class="text-xs text-base-content/40">{{ $date }}</span>
                    </div>
                @endif
            </div>
            {{-- Away --}}
            <div class="flex items-center gap-3 flex-1 justify-start">
                <img src="{{ $away->crest }}" alt="{{ $away->shortName }}" class="w-10 h-10 object-contain" />
                <span class="font-semibold">{{ $away->name }}</span>
            </div>
            {{-- Bet --}}
            <div class="divider divider-horizontal mx-0"></div>
            <div class="shrink-0">
                <livewire:place-bet :game="$game" :key="'desk-bet-'.$game->id" />
            </div>
        </div>
    </div>
</div>
