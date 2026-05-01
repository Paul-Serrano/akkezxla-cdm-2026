<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/**
 * Temporary diagnostic route — remove before production.
 * Probes The Odds API for FIFA World Cup 2026 data.
 *
 * Step 1: GET /v4/sports?all=true       — find World Cup sport key (free, no quota cost)
 * Step 2: GET /v4/sports/{key}/events   — list matches (free, no quota cost)
 * Step 3: GET /v4/sports/{key}/odds     — h2h odds, EU region (costs 1 credit)
 */
Route::get('/test-odds-api', function () {
    $apiKey = config('services.odds_api.key');

    if (empty($apiKey)) {
        return response()->json(['error' => 'ODDS_API_KEY is not set'], 500);
    }

    $base = 'https://api.the-odds-api.com/v4';

    // Step 1 — full sports list including out-of-season (free)
    $sportsResponse = Http::get("{$base}/sports", [
        'apiKey' => $apiKey,
        'all'    => 'true',
    ]);

    if ($sportsResponse->failed()) {
        return response()->json([
            'error'  => 'Failed to fetch sports list',
            'status' => $sportsResponse->status(),
            'body'   => $sportsResponse->json(),
        ], $sportsResponse->status());
    }

    $worldCupSport = collect($sportsResponse->json())
        ->first(fn ($s) => str_contains(strtolower($s['title'] ?? ''), 'soccer_fifa_world_cup')
            || str_contains(strtolower($s['key'] ?? ''), 'soccer_fifa_world_cup'));

    if (! $worldCupSport) {
        return response()->json([
            'message'         => 'World Cup not listed yet — showing all available soccer sports',
            'all_soccer'      => collect($sportsResponse->json())
                ->filter(fn ($s) => ($s['group'] ?? '') === 'Soccer')
                ->values(),
            'quota_remaining' => $sportsResponse->header('x-requests-remaining'),
        ]);
    }

    $sportKey = $worldCupSport['key'];

    // Step 2 — events / matches (free)
    $eventsResponse = Http::get("{$base}/sports/{$sportKey}/events", [
        'apiKey' => $apiKey,
    ]);

    // // Step 3 — h2h odds, EU bookmakers (costs 1 credit)
    // $oddsResponse = Http::get("{$base}/sports/{$sportKey}/odds", [
    //     'apiKey'     => $apiKey,
    //     'regions'    => 'eu',
    //     'markets'    => 'h2h',
    //     'oddsFormat' => 'decimal',
    // ]);

    return response()->json([
        'sport'  => $worldCupSport,
        'events' => $eventsResponse->json(),
    ]);
});

/**
 * Temporary diagnostic route — remove before production.
 * Probes football-data.org for FIFA World Cup 2026 groups, standings and matches.
 *
 * GET /v4/competitions/WC/standings?season=2026 — group tables (free)
 * GET /v4/competitions/WC/matches?season=2026   — all fixtures with stage/group (free)
 * GET /v4/competitions/WC/teams?season=2026     — all 32 teams (free)
 */
Route::get('/test-football-data', function () {
    $apiKey = config('services.football_data.key');
    $base   = config('services.football_data.base');

    if (empty($apiKey)) {
        return response()->json(['error' => 'FOOTBALL_DATA_API_KEY is not set'], 500);
    }

    $headers = ['X-Auth-Token' => $apiKey];

    [$standingsResponse, $matchesResponse, $teamsResponse, $scorersResponse, $playersResponse] = [
        Http::withHeaders($headers)->get("{$base}/competitions/WC/standings", ['season' => 2026]),
        Http::withHeaders($headers)->get("{$base}/competitions/WC/matches",   ['season' => 2026]),
        Http::withHeaders($headers)->get("{$base}/competitions/WC/teams",     ['season' => 2026]),
        Http::withHeaders($headers)->get("{$base}/competitions/WC/scorers",  ['season' => 2026]),
        Http::withHeaders($headers)->get("{$base}/competitions/WC/players",     ['season' => 2026]),
    ];

    // Extract group composition from the standings response
    $groups = collect($standingsResponse->json()['standings'] ?? [])
        ->filter(fn ($s) => $s['type'] === 'TOTAL')
        ->mapWithKeys(fn ($s) => [
            $s['group'] => collect($s['table'])->map(fn ($row) => [
                'position' => $row['position'],
                'team'     => [
                    'id'    => $row['team']['id'],
                    'name'  => $row['team']['name'],
                    'crest' => $row['team']['crest'] ?? null,
                ],
                'played' => $row['playedGames'],
                'won'    => $row['won'],
                'draw'   => $row['draw'],
                'lost'   => $row['lost'],
                'gf'     => $row['goalsFor'],
                'ga'     => $row['goalsAgainst'],
                'gd'     => $row['goalDifference'],
                'points' => $row['points'],
            ])->values(),
        ]);

    return response()->json([
        'groups'    => $groups,
        'matches'   => $matchesResponse->json(),
        'teams'     => $teamsResponse->json(),
        'standings' => $standingsResponse->json(),
        'scorers'   => $scorersResponse->json(),
        'players'   => $playersResponse->json(),
    ]);
});
