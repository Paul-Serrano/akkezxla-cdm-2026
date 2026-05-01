<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Standing;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportGames extends Command
{
    protected $signature = 'import:games';

    protected $description = 'Import FIFA World Cup 2026 matches from football-data.org and assign teams to group standings';

    public function handle(): int
    {
        $apiKey = config('services.football_data.key');

        if (empty($apiKey)) {
            $this->error('FOOTBALL_DATA_API_KEY is not set in your .env file.');
            return self::FAILURE;
        }

        $this->info('Fetching matches from football-data.org...');

        $response = Http::withHeaders([
            'X-Auth-Token' => $apiKey,
        ])->get('https://api.football-data.org/v4/competitions/WC/matches', [
            'season' => 2026,
        ]);

        if ($response->failed()) {
            $this->error("API request failed: {$response->status()} {$response->body()}");
            return self::FAILURE;
        }

        $matches = $response->json('matches', []);

        if (empty($matches)) {
            $this->warn('No matches returned from the API.');
            return self::SUCCESS;
        }

        // Build a cache of group standings keyed by letter (A, B, ..., L)
        $standingCache = [];

        // Build a cache of teams keyed by apiId
        $teamCache = Team::whereNotNull('apiId')->get()->keyBy('apiId');

        $this->info('Importing ' . count($matches) . ' matches...');
        $bar = $this->output->createProgressBar(count($matches));
        $bar->start();

        foreach ($matches as $match) {
            $group = $match['group'] ?? null; // e.g. "GROUP_A"
            $groupLetter = $group ? str_replace('GROUP_', '', $group) : null;

            // Resolve or create the standing for this group
            if ($groupLetter && !isset($standingCache[$groupLetter])) {
                $standingCache[$groupLetter] = Standing::firstOrCreate(
                    ['name' => $groupLetter],
                    ['apiId' => null]
                );
            }

            $standing = $groupLetter ? $standingCache[$groupLetter] : null;

            // Resolve home and away teams, reassigning their standing if needed
            $homeTeamApiId = $match['homeTeam']['id'] ?? null;
            $awayTeamApiId = $match['awayTeam']['id'] ?? null;

            $homeTeam = $homeTeamApiId ? $teamCache->get($homeTeamApiId) : null;
            $awayTeam = $awayTeamApiId ? $teamCache->get($awayTeamApiId) : null;

            if ($standing) {
                if ($homeTeam && $homeTeam->standingId !== $standing->id) {
                    $homeTeam->standingId = $standing->id;
                    $homeTeam->save();
                }
                if ($awayTeam && $awayTeam->standingId !== $standing->id) {
                    $awayTeam->standingId = $standing->id;
                    $awayTeam->save();
                }
            }

            if (!$homeTeam || !$awayTeam) {
                $bar->advance();
                continue;
            }

            $scoreHome = $match['score']['fullTime']['home'] ?? null;
            $scoreAway = $match['score']['fullTime']['away'] ?? null;

            Game::updateOrCreate(
                ['apiId' => $match['id']],
                [
                    'startDate'  => $match['utcDate'],
                    'scoreHome'  => $scoreHome,
                    'scoreAway'  => $scoreAway,
                    'homeTeamId' => $homeTeam->id,
                    'awayTeamId' => $awayTeam->id,
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Games imported successfully.');

        return self::SUCCESS;
    }
}
