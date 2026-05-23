<div class="space-y-4">
    <div class="flex items-center gap-2 min-w-0">
        <img src="{{ $team->crest }}" alt="{{ $team->shortName }}" class="w-7 h-7 object-contain shrink-0" />
        <h3 class="font-semibold truncate">{{ $team->name }} players</h3>
    </div>

    @if ($rows->isEmpty())
        <div class="text-sm text-base-content/60">No players found for this team.</div>
    @else
        <div class="overflow-x-auto">
            <table class="table table-sm w-full">
                <thead>
                    <tr class="text-base-content/50 text-xs uppercase">
                        <th>Player</th>
                        <th class="w-36">Position</th>
                        <th class="w-16">Age</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td class="font-medium text-sm">{{ $row['name'] }}</td>
                            <td class="text-xs">{{ $row['role'] ?: 'N/A' }}</td>
                            <td class="text-xs tabular-nums">{{ $row['age'] ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
