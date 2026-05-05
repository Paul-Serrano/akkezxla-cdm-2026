{{-- Consensus macro (reused in both layouts) --}}

{{-- MOBILE --}}
<div class="block md:hidden">
    <div class="card card-compact bg-base-100 shadow border border-base-200 w-full relative">
        <div class="card-body">
            @if ($isAdmin)
                <button wire:click="startEditScore({{ $game->id }})" class="btn btn-xs btn-ghost absolute top-1 left-1 z-10 opacity-40 hover:opacity-100">
                    <x-icon name="o-pencil-square" class="w-3 h-3" />
                </button>
            @endif
            <div class="flex items-center justify-center gap-2 mb-1">
                @if ($group)
                    <span class="text-xs font-medium badge badge-xs badge-info">Groupe {{ $group }}</span>
                    <span class="text-base-content/20">·</span>
                @endif
                <span class="text-xs text-base-content/50">{{ $date }}</span>
                <span class="badge badge-xs {{ $gameStatus->badgeClass() }}">{{ $gameStatus->label() }}</span>
            </div>
            <div class="flex items-center justify-between gap-2">
                {{-- Home --}}
                <div class="flex flex-col items-center gap-1 flex-1">
                    <img src="{{ $home->crest }}" alt="{{ $home->shortName }}" class="w-10 h-10 object-contain" />
                    <span class="text-xs font-medium text-center leading-tight">{{ $homeRank ? '#' . $homeRank . ' -' : '' }} {{ $home->shortName }}</span>
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
                    <span class="text-xs font-medium text-center leading-tight">{{ $awayRank ? '#' . $awayRank . ' -' : '' }} {{ $away->shortName }}</span>
                </div>
            </div>
            <div class="divider my-0"></div>
            {{-- Consensus bet (admin/winamax) --}}
            @if ($canSeeConsensus)
                <div class="flex flex-wrap items-center justify-center gap-1 mb-1">
                    @if ($consensus['total'] === 0)
                        <span class="text-xs text-base-content/30 italic">No consensus bet</span>
                    @else
                        <span class="text-xs text-base-content/40 me-1">Consensus</span>
                        @foreach ($consensus['outcomes'] as $top)
                            <span class="badge badge-sm gap-1 {{ $top['result']->badgeClass() }}">
                                <x-icon name="{{ $top['result']->icon() }}" class="w-3 h-3" />
                                {{ $top['label'] }}
                                <span class="opacity-60 text-xs">×{{ $top['count'] }}</span>
                            </span>
                        @endforeach
                    @endif
                </div>
            @endif
            <livewire:place-bet :game="$game" :key="'mob-bet-'.$game->id" />
        </div>
    </div>
</div>

{{-- DESKTOP --}}
<div class="hidden md:block">
    <div class="card bg-base-100 shadow border border-base-200 w-full relative">
        <div class="card-body flex-row items-center gap-4 py-4 px-6">
            @if ($isAdmin)
                <button wire:click="startEditScore({{ $game->id }})" class="btn btn-xs btn-ghost top-1 left-1 z-10 opacity-40 hover:opacity-100">
                    <x-icon name="o-pencil-square" class="w-3 h-3" />
                </button>
            @endif
            {{-- Home --}}
            <div>
            @if ($group)
                <span class="text-xs font-medium badge badge-xs badge-info">Groupe {{ $group }}</span>
            @endif
            </div>
            <div class="flex items-center gap-3 flex-1 justify-end">
                <div class="flex flex-col items-end">
                    <span class="font-semibold text-right">{{ $homeRank ? '#' . $homeRank . ' -' : '' }} {{ $home->name }}</span>
                </div>
                <img src="{{ $home->crest }}" alt="{{ $home->shortName }}" class="w-10 h-10 object-contain" />
            </div>
            {{-- Score / VS --}}
            <div class="flex flex-col items-center gap-0.5 shrink-0 min-w-[100px] justify-center">
                <div class="flex items-center gap-3">
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
            </div>
            {{-- Away --}}
            <div class="flex items-center gap-3 flex-1 justify-start">
                <img src="{{ $away->crest }}" alt="{{ $away->shortName }}" class="w-10 h-10 object-contain" />
                <div class="flex flex-col items-start">
                    <span class="font-semibold">{{ $awayRank ? '#' . $awayRank . ' -' : '' }} {{ $away->name }}</span>
                </div>
            </div>
            {{-- Bet --}}
            <div class="divider divider-horizontal mx-0"></div>
            <div class="shrink-0 flex flex-col items-center gap-2">
                {{-- Consensus bet (admin/winamax) --}}
                @if ($canSeeConsensus)
                    <div class="flex flex-wrap items-center justify-center gap-1">
                        @if ($consensus['total'] === 0)
                            <span class="text-xs text-base-content/30 italic">No bets yet</span>
                        @else
                            <span class="text-xs text-base-content/40 me-0.5">Consensus</span>
                            @foreach ($consensus['outcomes'] as $top)
                                <span class="badge badge-sm gap-1 {{ $top['result']->badgeClass() }}">
                                    <x-icon name="{{ $top['result']->icon() }}" class="w-3 h-3" />
                                    {{ $top['label'] }}
                                    <span class="opacity-60 text-xs">×{{ $top['count'] }}</span>
                                </span>
                            @endforeach
                        @endif
                    </div>
                @endif
                <livewire:place-bet :game="$game" :key="'desk-bet-'.$game->id" />
            </div>
        </div>
    </div>
</div>
