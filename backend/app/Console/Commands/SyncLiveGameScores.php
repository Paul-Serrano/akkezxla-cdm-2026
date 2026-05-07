<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Standing;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncLiveGameScores extends Command
{
    protected $signature = 'import:live-games {--season=2026 : Season to check}';

    protected $description = 'Sync scores for games currently in progress (auto-stops when all games finish)';

    public function handle(): int
    {
        $apiKey = config('services.football_data.key');
        $season = (int) $this->option('season');

        if (empty($apiKey)) {
            $this->error('FOOTBALL_DATA_API_KEY is not set in your .env file.');
            return self::FAILURE;
        }

        // Find local games that are in progress: started but don't have final scores yet.
        $liveGames = Game::whereNotNull('startDate')
            ->where('startDate', '<=', now())
            ->where(function ($q) {
                $q->whereNull('scoreHome')
                  ->orWhereNull('scoreAway');
            })
            ->get();

        if ($liveGames->isEmpty()) {
            $this->line('No games currently in progress.');
            return self::SUCCESS;
        }

        $this->info("Found {$liveGames->count()} game(s) in progress. Fetching updates...");

        // Fetch all matches from API for this season
        $response = Http::withHeaders([
            'X-Auth-Token' => $apiKey,
        ])->get('https://api.football-data.org/v4/competitions/WC/matches', [
            'season' => $season,
        ]);

        if ($response->failed()) {
            $this->error("API request failed: {$response->status()}");
            return self::FAILURE;
        }

        $allMatches = $response->json('matches', []);
        $liveApiIds = $liveGames->pluck('apiId')->toArray();

        // Filter to only the live games we care about
        $liveMatches = array_filter(
            $allMatches,
            fn ($m) => in_array($m['id'], $liveApiIds, true)
        );

        if (empty($liveMatches)) {
            $this->warn('Live games not found in API response.');
            return self::SUCCESS;
        }

        $this->info("Updating " . count($liveMatches) . " live game(s)...");
        $standingIdsToRecalculate = [];

        foreach ($liveMatches as $match) {
            $game = $liveGames->firstWhere('apiId', $match['id']);
            if (!$game) {
                continue;
            }

            // Extract scores (try fullTime, then regularTime)
            $scoreHome = $match['score']['fullTime']['home']
                ?? $match['score']['regularTime']['home']
                ?? null;
            $scoreAway = $match['score']['fullTime']['away']
                ?? $match['score']['regularTime']['away']
                ?? null;

            // Only update if match is finished or awarded
            $status = strtoupper((string) ($match['status'] ?? ''));
            if (!in_array($status, ['FINISHED', 'AWARDED'], true)) {
                $scoreHome = null;
                $scoreAway = null;
            }

            // Update game if scores changed
            if ($game->scoreHome !== $scoreHome || $game->scoreAway !== $scoreAway) {
                $game->update([
                    'scoreHome' => $scoreHome,
                    'scoreAway' => $scoreAway,
                ]);

                $this->line("  Updated: {$game->homeTeam->shortName} {$scoreHome} - {$scoreAway} {$game->awayTeam->shortName}");
            }

            // Mark standing for recalculation
            if ($game->homeTeam->standingId) {
                $standingIdsToRecalculate[$game->homeTeam->standingId] = true;
            }
            if ($game->awayTeam->standingId) {
                $standingIdsToRecalculate[$game->awayTeam->standingId] = true;
            }
        }

        // Recalculate affected standings
        if (!empty($standingIdsToRecalculate)) {
            foreach (array_keys($standingIdsToRecalculate) as $standingId) {
                Standing::recalculate((int) $standingId);
            }
            $this->info('Standings recalculated.');
        }

        $this->info('Live games synced successfully.');
        return self::SUCCESS;
    }
}
