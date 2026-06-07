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

    <div class="md:hidden space-y-3">
        @forelse ($bets as $bet)
            <div class="card bg-base-100 shadow border border-base-200">
                <div class="card-body p-4 gap-3">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold">Page {{ $bet->matchdayPage }}</span>
                        <span class="badge {{ $bet->status->badgeClass() }}">{{ $bet->status->label() }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <p class="text-base-content/60">Total Odds</p>
                            <p class="font-medium">{{ number_format((float) $bet->totalOdds, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-base-content/60">Amount</p>
                            <p class="font-medium">{{ number_format((float) $bet->amountBet, 2) }}€</p>
                        </div>
                        <div>
                            <p class="text-base-content/60">Earning</p>
                            <p class="font-medium">{{ $bet->earning !== null ? number_format((float) $bet->earning, 2) . '€' : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-base-content/60">Updated By</p>
                            <p class="font-medium">{{ $bet->user?->alias ?: ($bet->user?->name ?? '-') }}</p>
                        </div>
                    </div>

                    <div class="divider my-0"></div>

                    <div class="space-y-2 grid grid-cols-2 gap-2 text-xs">
                        @foreach ($bet->games as $game)
                            @php($consensus = $game->consensus())
                            <div class="text-xs space-y-2">
                                <p class="font-medium">{{ $game->homeTeam?->shortName ?? 'Home' }} vs {{ $game->awayTeam?->shortName ?? 'Away' }} : </p>
                                @if ($consensus['total'] === 0)
                                    <span class="text-base-content/40 italic">No consensus</span>
                                @else
                                    <button
                                        wire:click="openConsensusModal({{ $game->id }})"
                                        class="flex flex-wrap gap-2 items-center rounded-md px-1 py-1 hover:bg-base-200/70"
                                        type="button"
                                        title="Show all akkezxla bets"
                                    >
                                        @foreach ($consensus['outcomes'] as $top)
                                            <span class="badge badge-sm gap-1 {{ $top['result']->badgeClass() }}">
                                                <x-icon name="{{ $top['result']->icon() }}" class="w-3 h-3" />
                                                {{ $top['label'] }}
                                                <span class="opacity-60">x{{ $top['count'] }}</span>
                                            </span>
                                        @endforeach
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="card bg-base-100 shadow border border-base-200">
                <div class="card-body p-4 text-center text-base-content/60">No Winamax bet yet.</div>
            </div>
        @endforelse
    </div>

    <div class="hidden md:block overflow-x-auto bg-base-100 rounded-box shadow border border-base-200">
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
                            <div class="flex flex-col gap-2 text-xs">
                                @foreach ($bet->games as $game)
                                    @php($consensus = $game->consensus())
                                    <div class="flex gap-1 items-center">
                                        <span>{{ $game->homeTeam?->shortName ?? 'Home' }} vs {{ $game->awayTeam?->shortName ?? 'Away' }} : </span>

                                        @if ($consensus['total'] === 0)
                                            <span class="text-base-content/40 italic">No consensus</span>
                                        @else
                                            <button
                                                wire:click="openConsensusModal({{ $game->id }})"
                                                class="flex flex-wrap gap-1 items-center rounded-md px-1 py-1 hover:bg-base-200/70 cursor-pointer"
                                                type="button"
                                                title="Show all akkezxla bets"
                                            >
                                                @foreach ($consensus['outcomes'] as $top)
                                                    <span class="badge badge-sm gap-1 {{ $top['result']->badgeClass() }}">
                                                        <x-icon name="{{ $top['result']->icon() }}" class="w-3 h-3" />
                                                        {{ $top['label'] }}
                                                        <span class="opacity-60">x{{ $top['count'] }}</span>
                                                    </span>
                                                @endforeach
                                            </button>
                                        @endif
                                    </div>
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

    @if ($consensusGameId)
        <dialog class="modal modal-open">
            <div class="modal-box max-w-lg">
                <h3 class="font-bold text-lg">Consensus Details</h3>
                <p class="text-sm text-base-content/60 mb-4">{{ $consensusGameTitle }}</p>

                <div class="max-h-96 overflow-y-auto border border-base-200 rounded-box">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Akkezxla User</th>
                                <th class="text-right">Bet</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($consensusRows as $row)
                                <tr>
                                    <td>{{ $row['user'] }}</td>
                                    <td class="text-right">
                                        @if ($row['hasBet'])
                                            <span class="badge badge-success">{{ $row['bet'] }}</span>
                                        @else
                                            <span class="badge badge-ghost">No bet</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="modal-action">
                    <x-button label="Close" wire:click="closeConsensusModal" />
                </div>
            </div>
            <div class="modal-backdrop" wire:click="closeConsensusModal"></div>
        </dialog>
    @endif
</div>
