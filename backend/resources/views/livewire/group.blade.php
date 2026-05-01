<x-card title="Group {{ $standing->name }}" shadow separator class="w-full">

    {{-- MOBILE --}}
    <div class="block md:hidden">
        <ul class="divide-y divide-base-200">
            @foreach ($teamsWithStats as $i => $row)
                @php $s = $row['stats']; $team = $row['team']; $gd = $s['gf'] - $s['ga']; @endphp
                <li class="flex items-center gap-2 py-2">
                    <span class="text-xs font-bold w-4 text-center text-base-content/40">{{ $i + 1 }}</span>
                    <img src="{{ $team->crest }}" alt="{{ $team->shortName }}" class="w-6 h-6 object-contain shrink-0" />
                    <span class="flex-1 text-sm font-medium truncate">{{ $team->shortName }}</span>
                    <div class="flex items-center gap-2 text-xs tabular-nums text-base-content/60">
                        <span title="Goals for" class="w-5 text-center">{{ $s['gf'] }}</span>
                        <span class="text-base-content/20">:</span>
                        <span title="Goals against" class="w-5 text-center">{{ $s['ga'] }}</span>
                        <span title="Goal difference" class="w-6 text-center {{ $gd > 0 ? 'text-emerald-500' : ($gd < 0 ? 'text-red-500' : '') }}">
                            {{ $gd > 0 ? '+' : '' }}{{ $gd }}
                        </span>
                        <span class="font-black text-base-content text-sm w-5 text-center">{{ $s['pts'] }}</span>
                    </div>
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
                    @php $s = $row['stats']; $team = $row['team']; $gd = $s['gf'] - $s['ga']; @endphp
                    <tr class="hover">
                        <td class="text-center text-base-content/40 font-bold text-xs">{{ $i + 1 }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <img src="{{ $team->crest }}" alt="{{ $team->shortName }}" class="w-6 h-6 object-contain" />
                                <span class="font-medium text-sm">{{ $team->name }}</span>
                            </div>
                        </td>
                        <td class="text-center tabular-nums text-xs">{{ $s['played'] }}</td>
                        <td class="text-center tabular-nums text-xs text-emerald-600">{{ $s['won'] }}</td>
                        <td class="text-center tabular-nums text-xs text-base-content/50">{{ $s['drawn'] }}</td>
                        <td class="text-center tabular-nums text-xs text-red-500">{{ $s['lost'] }}</td>
                        <td class="text-center tabular-nums text-xs">{{ $s['gf'] }}</td>
                        <td class="text-center tabular-nums text-xs">{{ $s['ga'] }}</td>
                        <td class="text-center tabular-nums text-xs font-semibold {{ $gd > 0 ? 'text-emerald-500' : ($gd < 0 ? 'text-red-500' : 'text-base-content/40') }}">
                            {{ $gd > 0 ? '+' : '' }}{{ $gd }}
                        </td>
                        <td class="text-center tabular-nums font-black text-base">{{ $s['pts'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</x-card>
