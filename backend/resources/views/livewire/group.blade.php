<x-card title="Group {{ $standing->name }}" shadow separator class="w-full">

    @if ($selectedTeam)
        <div class="space-y-4">
            <div class="flex items-center justify-end gap-3">
                <button type="button" wire:click="showRanking" class="btn btn-sm btn-outline">Back</button>
            </div>

            <livewire:team-player :team="$selectedTeam" :key="'team-player-'.$selectedTeam->id" />
        </div>
    @else
        {{-- MOBILE --}}
        <div class="block md:hidden">
            <ul class="divide-y divide-base-200">
                @foreach ($teamsWithStats as $i => $row)
                    <li>
                        <button type="button" wire:click="showPlayers({{ $row['team']->id }})" class="w-full flex items-center gap-2 py-2 text-left">
                            <span class="text-xs font-bold w-4 text-center text-base-content/40">{{ $i + 1 }}</span>
                            <img src="{{ $row['team']->crest }}" alt="{{ $row['team']->shortName }}" class="w-6 h-6 object-contain shrink-0" />
                            <span class="flex-1 text-sm font-medium truncate">{{ $row['team']->shortName }}</span>
                            <div class="flex items-center gap-2 text-xs tabular-nums text-base-content/60">
                                <span title="Goals for" class="w-5 text-center">{{ $row['stats']['gf'] }}</span>
                                <span class="text-base-content/20">:</span>
                                <span title="Goals against" class="w-5 text-center">{{ $row['stats']['ga'] }}</span>
                                <span title="Goal difference" class="w-6 text-center {{ $row['gdClass'] }}">
                                    {{ $row['gdLabel'] }}
                                </span>
                                <span class="font-black text-base-content text-sm w-5 text-center">{{ $row['stats']['pts'] }}</span>
                            </div>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- DESKTOP --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="table table-sm w-full">
                <thead>
                    <tr class="text-base-content/50 text-xs uppercase">
                        <th class="w-6 text-center">#</th>
                        <th>Team</th>
                        <th class="text-center w-8" title="Played">P</th>
                        <th class="text-center w-8" title="Won">W</th>
                        <th class="text-center w-8" title="Drawn">D</th>
                        <th class="text-center w-8" title="Lost">L</th>
                        <th class="text-center w-8" title="Goals for">GF</th>
                        <th class="text-center w-8" title="Goals against">GA</th>
                        <th class="text-center w-8" title="Goal difference">GD</th>
                        <th class="text-center w-8 text-base-content font-bold" title="Points">Pts</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($teamsWithStats as $i => $row)
                        <tr class="hover cursor-pointer" wire:click="showPlayers({{ $row['team']->id }})">
                            <td class="text-center text-base-content/40 font-bold text-xs">{{ $i + 1 }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <img src="{{ $row['team']->crest }}" alt="{{ $row['team']->shortName }}" class="w-6 h-6 object-contain" />
                                    <span class="font-medium text-sm">{{ $row['team']->name }}</span>
                                </div>
                            </td>
                            <td class="text-center tabular-nums text-xs">{{ $row['stats']['played'] }}</td>
                            <td class="text-center tabular-nums text-xs text-emerald-600">{{ $row['stats']['won'] }}</td>
                            <td class="text-center tabular-nums text-xs text-base-content/50">{{ $row['stats']['drawn'] }}</td>
                            <td class="text-center tabular-nums text-xs text-red-500">{{ $row['stats']['lost'] }}</td>
                            <td class="text-center tabular-nums text-xs">{{ $row['stats']['gf'] }}</td>
                            <td class="text-center tabular-nums text-xs">{{ $row['stats']['ga'] }}</td>
                            <td class="text-center tabular-nums text-xs font-semibold {{ $row['gdClassRow'] }}">
                                {{ $row['gdLabel'] }}
                            </td>
                            <td class="text-center tabular-nums font-black text-base">{{ $row['stats']['pts'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</x-card>
