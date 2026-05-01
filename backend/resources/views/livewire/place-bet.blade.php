<div>
    @auth
        {{-- Status badges --}}
        <div class="flex flex-wrap items-center gap-1 justify-center mb-2">
            <span class="badge badge-xs {{ $betStatus->badgeClass() }}">
                {{ $betStatus->label() }}
            </span>
            @if ($betResult !== \App\Enums\BetResult::Pending || $betStatus === \App\Enums\BetStatus::Placed)
                <span class="badge badge-xs {{ $betResult->badgeClass() }} flex items-center gap-0.5">
                    <x-icon name="{{ $betResult->icon() }}" class="w-3 h-3 {{ $betResult->textClass() }}" />
                    {{ $betResult->label() }}
                </span>
            @endif
        </div>

        @if ($gameStatus === \App\Enums\GameStatus::Ended)
            {{-- Locked: game is over --}}
            @if ($betStatus === \App\Enums\BetStatus::Placed)
                <p class="text-sm tabular-nums font-semibold text-base-content/60 text-center">
                    {{ $scoreHome }} &mdash; {{ $scoreAway }}
                </p>
            @else
                <p class="text-xs text-base-content/30 text-center italic">No bet placed</p>
            @endif
        @else
            {{-- Active bet form --}}
            <form wire:submit="save" class="flex items-center gap-2 mt-2 flex-wrap justify-center">
                <div class="flex items-center gap-1">
                    <input
                        type="number"
                        min="0"
                        max="99"
                        wire:model="scoreHome"
                        placeholder="0"
                        class="input input-bordered input-sm w-14 text-center tabular-nums"
                    />
                    <span class="text-base-content/40 font-light">—</span>
                    <input
                        type="number"
                        min="0"
                        max="99"
                        wire:model="scoreAway"
                        placeholder="0"
                        class="input input-bordered input-sm w-14 text-center tabular-nums"
                    />
                </div>

                <button type="submit" class="btn btn-primary btn-sm gap-1">
                    <x-icon name="o-bookmark" class="w-4 h-4" />
                    <span class="hidden sm:inline">Bet</span>
                </button>

                @if ($saved)
                    <span
                        class="text-success text-xs flex items-center gap-1"
                        x-data
                        x-init="setTimeout(() => $wire.set('saved', false), 2000)"
                    >
                        <x-icon name="o-check-circle" class="w-4 h-4" />
                        Saved
                    </span>
                @endif
            </form>

            @error('scoreHome') <p class="text-error text-xs text-center mt-1">{{ $message }}</p> @enderror
            @error('scoreAway') <p class="text-error text-xs text-center mt-1">{{ $message }}</p> @enderror
        @endif

    @else
        <p class="text-xs text-base-content/40 text-center mt-2">
            <a href="{{ route('login') }}" class="link link-primary">Login</a> to place your bet
        </p>
    @endauth
</div>
