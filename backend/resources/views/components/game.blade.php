@props(['game'])

@php
    use App\Enums\BetResult;
    use App\Enums\GameStatus;
    use App\Models\Bet;
    use App\Models\User;

    $home       = $game->homeTeam;
    $away       = $game->awayTeam;
    $gameStatus = GameStatus::fromGame($game);
    $played     = $gameStatus === GameStatus::Ended;
    $date       = \Carbon\Carbon::parse($game->startDate)->format('d M · H:i');

    // Consensus bet (admin/winamax only, visible only to those roles)
    $canSeeConsensus = auth()->check()
        && in_array(auth()->user()->role, [User::ROLE_ADMIN, User::ROLE_WINAMAX]);

    $topBets    = collect();
    $totalBets  = 0;

    if ($canSeeConsensus) {
        $allBets = Bet::whereHas('user', fn($q) => $q->whereIn('role', [User::ROLE_ADMIN, User::ROLE_WINAMAX]))
            ->where('gameId', $game->id)
            ->whereNull('playerId')
            ->get();

        $totalBets = $allBets->count();

        if ($totalBets > 0) {
            $grouped  = $allBets->groupBy('bet')->sortByDesc(fn($g) => $g->count());
            $maxCount = $grouped->first()->count();
            $topBets  = $grouped->filter(fn($g) => $g->count() === $maxCount);
        }
    }
@endphp

{{-- Consensus macro (reused in both layouts) --}}
@if ($canSeeConsensus)
    @php
        // Pre-compute result tags for each top bet
        $topBetResults = $topBets->map(function ($group) use ($game, $played) {
            $bet    = $group->first();
            $result = $played
                ? BetResult::compute($bet->scoreHome, $bet->scoreAway, $game)
                : BetResult::Pending;
            return [
                'score'  => $bet->bet,
                'count'  => $group->count(),
                'result' => $result,
            ];
        })->values();
    @endphp
@endif

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
            {{-- Consensus bet (admin/winamax) --}}
            @if ($canSeeConsensus)
                <div class="flex flex-wrap items-center justify-center gap-1 mb-1">
                    @if ($totalBets === 0)
                        <span class="text-xs text-base-content/30 italic">No consensus bet</span>
                    @else
                        <span class="text-xs text-base-content/40 me-1">Consensus</span>
                        @foreach ($topBetResults as $top)
                            <span class="badge badge-sm font-mono gap-1 {{ $top['result']->badgeClass() }}">
                                <x-icon name="{{ $top['result']->icon() }}" class="w-3 h-3" />
                                {{ $top['score'] }}
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
            <div class="shrink-0 flex flex-col items-center gap-2">
                {{-- Consensus bet (admin/winamax) --}}
                @if ($canSeeConsensus)
                    <div class="flex flex-wrap items-center justify-center gap-1">
                        @if ($totalBets === 0)
                            <span class="text-xs text-base-content/30 italic">No bets yet</span>
                        @else
                            <span class="text-xs text-base-content/40 me-0.5">Consensus</span>
                            @foreach ($topBetResults as $top)
                                <span class="badge badge-sm font-mono gap-1 {{ $top['result']->badgeClass() }}">
                                    <x-icon name="{{ $top['result']->icon() }}" class="w-3 h-3" />
                                    {{ $top['score'] }}
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
