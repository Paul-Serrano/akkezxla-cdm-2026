<div x-on:bet-placed.window="$wire.refreshGames()">
    @if ($canManageWinamaxBet)
        <div class="card bg-base-100 shadow border border-base-200 mb-4">
            <div class="card-body gap-3">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="card-title text-base">Winamax Bet - Page {{ $matchday }}</h2>
                    @if ($winamaxSaved)
                        <span class="badge badge-success">Saved</span>
                    @endif
                </div>

                @if (!$pageHasExactlyFourGames)
                    <x-alert
                        title="This page does not contain exactly 4 games. Winamax bet cannot be saved yet."
                        icon="o-information-circle"
                        class="alert-warning"
                    />
                @else
                    <p class="text-xs text-base-content/60">
                        Games: {{ implode(' | ', $winamaxGamesSummary) }}
                    </p>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                    <label class="form-control">
                        <span class="label-text">Total odds</span>
                        <input
                            type="number"
                            step="0.01"
                            min="1"
                            wire:model="winamaxTotalOdds"
                            class="input input-bordered"
                            placeholder="e.g. 8.45"
                        />
                    </label>

                    <label class="form-control">
                        <span class="label-text">Amount bet</span>
                        <input
                            type="number"
                            step="0.01"
                            min="0.01"
                            wire:model="winamaxAmountBet"
                            class="input input-bordered"
                            placeholder="e.g. 20"
                        />
                    </label>

                    <label class="form-control">
                        <span class="label-text">Earning</span>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            wire:model="winamaxEarning"
                            class="input input-bordered"
                            placeholder="e.g. 52.40"
                        />
                    </label>

                    <label class="form-control">
                        <span class="label-text">Status</span>
                        <select wire:model="winamaxStatus" class="select select-bordered">
                            @foreach ($winamaxStatusOptions as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </label>

                    <x-button
                        label="Save Winamax Bet"
                        class="btn-primary"
                        wire:click="saveWinamaxBet"
                        :disabled="!$pageHasExactlyFourGames"
                    />
                </div>

                @error('winamaxTotalOdds') <p class="text-error text-xs">{{ $message }}</p> @enderror
                @error('winamaxAmountBet') <p class="text-error text-xs">{{ $message }}</p> @enderror
                @error('winamaxEarning') <p class="text-error text-xs">{{ $message }}</p> @enderror
                @error('winamaxStatus') <p class="text-error text-xs">{{ $message }}</p> @enderror
                @error('winamaxBet') <p class="text-error text-xs">{{ $message }}</p> @enderror
            </div>
        </div>
    @endif

    {{-- Header with page navigation --}}
    <x-header separator>
        <x-slot:title>
            Match Page {{ $matchday }}
        </x-slot:title>
        <x-slot:actions>
            <div class="join">
                <x-button
                    icon="o-chevron-left"
                    wire:click="$set('matchday', {{ $previousMatchday }})"
                    :disabled="$isFirstDay"
                    class="join-item btn-sm"
                    tooltip="Previous page"
                />
                <x-button
                    icon="o-chevron-right"
                    wire:click="$set('matchday', {{ $nextMatchday }})"
                    :disabled="$isLastDay"
                    class="join-item btn-sm"
                    tooltip="Next page"
                />
            </div>
        </x-slot:actions>
    </x-header>

    @if (!$hasGames)
        <x-alert title="No games found for this page." icon="o-information-circle" class="alert-info" />
    @else
        {{-- MOBILE: stacked cards --}}
        <div class="block md:hidden">
            @foreach ($games as $game)
                <div wire:key="mob-game-{{ $game->id }}-{{ $betRenderNonce }}">
                    <x-game :game="$game" :bet-refresh-key="$betRenderNonce" />
                </div>
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
        <div class="hidden md:flex flex-col gap-4 mx-auto">
            @foreach ($games as $game)
                <div wire:key="desk-game-{{ $game->id }}-{{ $betRenderNonce }}">
                    <x-game :game="$game" :bet-refresh-key="$betRenderNonce" />
                </div>
            @endforeach
        </div>
    @endif

    {{-- Admin: edit score modal --}}
    @if ($editGameId)
        <dialog class="modal modal-open">
            <div class="modal-box max-w-xs">
                <h3 class="font-bold text-lg mb-4">Update Score</h3>
                <div class="flex items-center justify-center gap-4">
                    <div class="flex flex-col items-center gap-1">
                        <img
                            src="{{ $editHomeCrest }}"
                            alt="{{ $editHomeAlt }}"
                            class="w-6 h-6 object-contain"
                        />
                        <input
                            type="number"
                            wire:model="editScoreHome"
                            min="0" max="99"
                            class="input input-bordered w-20 text-center text-2xl font-bold"
                        />
                    </div>
                    <span class="text-2xl font-light text-base-content/40">—</span>
                    <div class="flex flex-col items-center gap-1">
                        <img
                            src="{{ $editAwayCrest }}"
                            alt="{{ $editAwayAlt }}"
                            class="w-6 h-6 object-contain"
                        />
                        <input
                            type="number"
                            wire:model="editScoreAway"
                            min="0" max="99"
                            class="input input-bordered w-20 text-center text-2xl font-bold"
                        />
                    </div>
                </div>
                @error('editScoreHome') <p class="text-error text-xs mt-2">{{ $message }}</p> @enderror
                @error('editScoreAway') <p class="text-error text-xs mt-2">{{ $message }}</p> @enderror
                <div class="modal-action">
                    <x-button label="Cancel" wire:click="cancelEditScore" />
                    <x-button label="Save" class="btn-primary" wire:click="saveScore" />
                </div>
            </div>
            <div class="modal-backdrop" wire:click="cancelEditScore"></div>
        </dialog>
    @endif

    {{-- Akkezxla: all bets for selected game --}}
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
