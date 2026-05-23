<x-layouts.app>
    <div class="space-y-6">
        <x-header
            title="Football Data API Explorer"
            subtitle="Quick test page to query football-data.org endpoints"
            separator
        />

        <div class="card bg-base-100 shadow">
            <div class="card-body gap-4">
                <form method="GET" action="{{ route('football-data.explorer') }}" class="space-y-4">
                    <label class="form-control w-full">
                        <div class="label">
                            <span class="label-text font-semibold">Endpoint</span>
                        </div>
                        <input
                            type="text"
                            name="endpoint"
                            value="{{ $endpoint }}"
                            class="input input-bordered w-full"
                            placeholder="competitions/WC/matches"
                        />
                        <div class="label">
                            <span class="label-text-alt">Examples: competitions, competitions/WC/teams, competitions/WC/matches</span>
                        </div>
                    </label>

                    <label class="form-control w-full">
                        <div class="label">
                            <span class="label-text font-semibold">Query string</span>
                        </div>
                        <input
                            type="text"
                            name="query"
                            value="{{ $rawQuery }}"
                            class="input input-bordered w-full"
                            placeholder="status=SCHEDULED&matchday=1"
                        />
                        <div class="label">
                            <span class="label-text-alt">Use standard query params (key=value&key2=value2)</span>
                        </div>
                    </label>

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="{{ route('football-data.explorer') }}" class="btn btn-ghost">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        @if ($error)
            <div class="alert alert-error">
                <span>{{ $error }}</span>
            </div>
        @endif

        @if (! is_null($status))
            <div class="card bg-base-100 shadow">
                <div class="card-body gap-4">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="card-title">Response</h2>
                        <span class="badge badge-outline">HTTP {{ $status }}</span>
                    </div>

                    <pre class="bg-base-200 rounded-xl p-4 overflow-x-auto text-xs md:text-sm">{{ json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
