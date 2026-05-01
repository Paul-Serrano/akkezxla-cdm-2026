<x-card title="Group {{ $standing->name }}" shadow separator class="w-full">

    {{-- MOBILE --}}
    <div class="block md:hidden">
        <ul class="divide-y divide-base-200">
            @foreach ($teams as $i => $team)
                <li class="flex items-center gap-3 py-2">
                    <span class="text-xs font-bold w-5 text-center text-base-content/50">{{ $i + 1 }}</span>
                    <img src="{{ $team->crest }}" alt="{{ $team->shortName }}" class="w-6 h-6 object-contain shrink-0" />
                    <span class="flex-1 text-sm font-medium truncate">{{ $team->shortName }}</span>
                    @if ($team->rank)
                        <x-badge value="{{ $team->rank }} pts" class="badge-ghost badge-sm" />
                    @endif
                </li>
            @endforeach
        </ul>
    </div>

    {{-- DESKTOP --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="table table-sm w-full">
            <thead>
                <tr>
                    <th class="w-8 text-center">#</th>
                    <th>Team</th>
                    <th class="text-center">Pts</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($teams as $i => $team)
                    <tr class="hover">
                        <td class="text-center text-base-content/50 font-bold">{{ $i + 1 }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <img src="{{ $team->crest }}" alt="{{ $team->shortName }}" class="w-7 h-7 object-contain" />
                                <span class="font-medium">{{ $team->name }}</span>
                            </div>
                        </td>
                        <td class="text-center font-semibold">{{ $team->rank ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</x-card>
