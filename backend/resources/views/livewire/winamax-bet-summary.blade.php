<div>
    <x-header separator>
        <x-slot:title>Winamax Bet</x-slot:title>
        <x-slot:subtitle>Summary of all Winamax bets</x-slot:subtitle>
    </x-header>

    <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-5">
        <div class="stat bg-base-100 rounded-box shadow border border-base-200">
            <div class="stat-title">Total Bets</div>
            <div class="stat-value text-2xl">{{ $bets->count() }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow border border-base-200">
            <div class="stat-title">Money Placed</div>
            <div class="stat-value text-2xl">{{ number_format($totalAmount, 2) }}€</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow border border-base-200">
            <div class="stat-title">Earnings (Won)</div>
            <div class="stat-value text-2xl">{{ number_format($totalWonEarnings, 2) }}€</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow border border-base-200">
            <div class="stat-title">Total Earned</div>
            <div class="stat-value text-2xl {{ $netEarned >= 0 ? 'text-success' : 'text-error' }}">{{ number_format($netEarned, 2) }}€</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow border border-base-200">
            <div class="stat-title">Pending</div>
            <div class="stat-value text-2xl">{{ $pendingCount }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow border border-base-200">
            <div class="stat-title">Placed</div>
            <div class="stat-value text-2xl">{{ $placedCount }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow border border-base-200">
            <div class="stat-title">Won / Lost</div>
            <div class="stat-value text-2xl">{{ $wonCount }} / {{ $lostCount }}</div>
        </div>
    </div>

    <div class="overflow-x-auto bg-base-100 rounded-box shadow border border-base-200">
        <table class="table table-zebra">
            <thead>
                <tr>
                    <th>Page</th>
                    <th>Games</th>
                    <th>Total Odds</th>
                    <th>Amount</th>
                    <th>Earning</th>
                    <th>Status</th>
                    <th>Updated By</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bets as $bet)
                    <tr>
                        <td class="font-semibold">{{ $bet->matchdayPage }}</td>
                        <td>
                            <div class="flex flex-col gap-1 text-xs">
                                @foreach ($bet->games as $game)
                                    <span>{{ $game->homeTeam?->shortName ?? 'Home' }} vs {{ $game->awayTeam?->shortName ?? 'Away' }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td>{{ number_format((float) $bet->totalOdds, 2) }}</td>
                        <td>{{ number_format((float) $bet->amountBet, 2) }}€</td>
                        <td>{{ $bet->earning !== null ? number_format((float) $bet->earning, 2) . '€' : '-' }}</td>
                        <td>
                            <span class="badge {{ $bet->status->badgeClass() }}">{{ $bet->status->label() }}</span>
                        </td>
                        <td>{{ $bet->user?->alias ?: ($bet->user?->name ?? '-') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-base-content/60">No Winamax bet yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
