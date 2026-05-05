<div>
    <x-header title="Ranking" separator>
        <x-slot:subtitle>
            Super Win: <strong>{{ $ptsSuperWin }} pts</strong> &bull;
            Win: <strong>{{ $ptsWin }} pt</strong> &bull;
            Scorer: <strong>{{ $ptsScorer }} pt</strong>
        </x-slot:subtitle>
    </x-header>

    @php
        $isDesc = $sortDir === 'desc';
        $col    = $sortBy;

        $columns = [
            'points'       => ['label' => 'Points',   'icon' => 'o-star',          'color' => 'text-amber-500'],
            'superWins'    => ['label' => 'Exact',     'icon' => 'o-check-badge',   'color' => 'text-amber-500'],
            'wins'         => ['label' => 'Result',    'icon' => 'o-check-circle',  'color' => 'text-emerald-500'],
            'bets'         => ['label' => 'Bets',      'icon' => 'o-bookmark',      'color' => 'text-sky-500'],
            'pointsPerBet' => ['label' => 'Pts/Bet',   'icon' => 'o-calculator',    'color' => 'text-violet-500'],
            'alias'        => ['label' => 'Name',      'icon' => 'o-user',          'color' => 'text-base-content'],
        ];
    @endphp

    {{-- Role filter --}}
    <div class="flex flex-wrap gap-2 mb-3">
        <button
            wire:click="$set('filterRole', '')"
            @class(['btn btn-sm gap-1 transition-all', 'btn-neutral shadow-md' => $filterRole === '', 'btn-ghost text-base-content/60' => $filterRole !== ''])
        >
            <x-icon name="o-users" class="w-4 h-4" />
            All
        </button>
        @foreach ($allRoles as $r)
            <button
                wire:click="$set('filterRole', '{{ $r->name }}')"
                @class(['btn btn-sm gap-1 transition-all', 'btn-neutral shadow-md' => $filterRole === $r->name, 'btn-ghost text-base-content/60' => $filterRole !== $r->name])
                style="background-color: {{ $r->color ?? '#000' }}; color: #fff;"
            >
                {{ $r->label }}
            </button>
        @endforeach
    </div>

    {{-- Sort selector (shared, above both layouts) --}}
    <div class="flex flex-wrap gap-2 mb-4">
        @foreach ($columns as $key => $meta)
            <button
                wire:click="sort('{{ $key }}')"
                @class([
                    'btn btn-sm gap-1 transition-all',
                    'btn-neutral shadow-md'  => $col === $key,
                    'btn-ghost text-base-content/60' => $col !== $key,
                ])
            >
                <x-icon name="{{ $meta['icon'] }}" @class(['w-4 h-4', $meta['color'] => $col === $key]) />
                {{ $meta['label'] }}
                @if ($col === $key)
                    <x-icon name="{{ $isDesc ? 'o-arrow-down' : 'o-arrow-up' }}" class="w-3 h-3" />
                @endif
            </button>
        @endforeach
    </div>

    {{-- DESKTOP --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="table table-zebra w-full">
            <thead>
                <tr>
                    <th class="w-10 text-base-content/40">#</th>
                    @foreach ($columns as $key => $meta)
                        <th @class(['text-center' => $key !== 'alias'])>
                            <button
                                wire:click="sort('{{ $key }}')"
                                @class([
                                    'flex items-center gap-1 transition-colors',
                                    'justify-center' => $key !== 'alias',
                                    'font-bold ' . $meta['color'] => $col === $key,
                                    'text-base-content/50 hover:text-base-content' => $col !== $key,
                                ])
                            >
                                <x-icon name="{{ $meta['icon'] }}" class="w-4 h-4" />
                                {{ $meta['label'] }}
                                @if ($col === $key)
                                    <x-icon name="{{ $isDesc ? 'o-arrow-down' : 'o-arrow-up' }}" class="w-3 h-3" />
                                @endif
                            </button>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr @class([
                        'bg-amber-50 dark:bg-amber-900/10' => $row['rank'] === 1 && $row['points'] > 0,
                        'current-user-row' => $row['id'] === $currentUserId,
                    ])>
                        <td>
                            @if ($row['rank'] === 1 && $row['points'] > 0)
                                <x-icon name="o-trophy" class="w-5 h-5 text-amber-400" />
                            @else
                                <span class="text-base-content/40 font-mono text-sm">{{ $row['rank'] }}</span>
                            @endif
                        </td>
                        {{-- points --}}
                        <td @class(['text-center', 'font-black text-lg text-amber-500' => $col === 'points', 'tabular-nums font-bold text-lg' => $col !== 'points'])>
                            {{ $row['points'] }}
                        </td>
                        {{-- superWins --}}
                        <td @class(['text-center tabular-nums', 'font-black text-amber-500' => $col === 'superWins', 'text-amber-400' => $col !== 'superWins'])>
                            {{ $row['superWins'] }}
                        </td>
                        {{-- wins --}}
                        <td @class(['text-center tabular-nums', 'font-black text-emerald-500' => $col === 'wins', 'text-emerald-400' => $col !== 'wins'])>
                            {{ $row['wins'] }}
                        </td>
                        {{-- bets --}}
                        <td @class(['text-center tabular-nums', 'font-black text-sky-500' => $col === 'bets', 'text-base-content/60' => $col !== 'bets'])>
                            {{ $row['bets'] }}
                        </td>
                        {{-- pointsPerBet --}}
                        <td @class(['text-center tabular-nums', 'font-black text-violet-500' => $col === 'pointsPerBet', 'text-base-content/50' => $col !== 'pointsPerBet'])>
                            {{ $row['pointsPerBet'] }}
                        </td>
                        {{-- alias --}}
                        <td>
                            <span @class(['font-semibold', 'text-base-content' => $col !== 'alias', 'font-black underline' => $col === 'alias'])>
                                {{ $row['alias'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + 1 }}" class="text-center text-base-content/40 py-8">
                            No finished games yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- MOBILE --}}
    <div class="flex flex-col gap-3 md:hidden">
        @forelse ($rows as $row)
            <div @class([
                'card card-compact bg-base-100 shadow border-2 transition-colors',
                'border-amber-400' => $row['rank'] === 1 && $row['points'] > 0,
                'border-primary'   => $row['id'] === $currentUserId && !($row['rank'] === 1 && $row['points'] > 0),
                'border-base-200'  => $row['id'] !== $currentUserId && !($row['rank'] === 1 && $row['points'] > 0),
            ])>
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            @if ($row['rank'] === 1 && $row['points'] > 0)
                                <x-icon name="o-trophy" class="w-7 h-7 text-amber-400 shrink-0" />
                            @else
                                <span class="text-2xl font-black tabular-nums text-base-content/25 w-8 shrink-0">#{{ $row['rank'] }}</span>
                            @endif
                            <div>
                                <p class="font-bold leading-tight">
                                    {{ $row['alias'] }}
                                </p>
                                <span class="badge badge-xs badge-ghost">{{ $row['role'] }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <p @class([
                                'text-3xl font-black tabular-nums',
                                'text-amber-500' => $col === 'points',
                            ])>{{ $row['points'] }}</p>
                            <p class="text-xs text-base-content/40">pts</p>
                        </div>
                    </div>
                    <div class="divider my-1"></div>
                    <div class="grid grid-cols-4 text-center text-xs gap-1">
                        <div @class(['rounded p-1', 'bg-amber-50 dark:bg-amber-900/20 ring-1 ring-amber-300' => $col === 'superWins'])>
                            <p @class(['font-bold tabular-nums', 'text-amber-500' => true])>{{ $row['superWins'] }}</p>
                            <p class="text-base-content/40">Exact</p>
                        </div>
                        <div @class(['rounded p-1', 'bg-emerald-50 dark:bg-emerald-900/20 ring-1 ring-emerald-300' => $col === 'wins'])>
                            <p @class(['font-bold tabular-nums', 'text-emerald-500' => true])>{{ $row['wins'] }}</p>
                            <p class="text-base-content/40">Result</p>
                        </div>
                        <div @class(['rounded p-1', 'bg-sky-50 dark:bg-sky-900/20 ring-1 ring-sky-300' => $col === 'bets'])>
                            <p @class(['font-bold tabular-nums', 'text-sky-500' => $col === 'bets', 'text-base-content/60' => $col !== 'bets'])>{{ $row['bets'] }}</p>
                            <p class="text-base-content/40">Bets</p>
                        </div>
                        <div @class(['rounded p-1', 'bg-violet-50 dark:bg-violet-900/20 ring-1 ring-violet-300' => $col === 'pointsPerBet'])>
                            <p @class(['font-bold tabular-nums', 'text-violet-500' => $col === 'pointsPerBet', 'text-base-content/50' => $col !== 'pointsPerBet'])>{{ $row['pointsPerBet'] }}</p>
                            <p class="text-base-content/40">Pts/Bet</p>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <x-alert title="No finished games yet." icon="o-information-circle" class="alert-info" />
        @endforelse
    </div>
</div>

