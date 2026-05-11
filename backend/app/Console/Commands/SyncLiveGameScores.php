<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Standing;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncLiveGameScores extends Command
{
    protected $signature = 'import:live-games {--season=2026 : Season to check} {--log-unchanged : Also log games whose score did not change}';

    protected $description = 'Sync scores for games currently in progress (auto-stops when all games finish)';

    public function handle(): int
    {
        $startedAt = microtime(true);
        $apiKey = config('services.football_data.key');
        $season = (int) $this->option('season');
        $logUnchanged = (bool) $this->option('log-unchanged');
        $startedMessage = sprintf(
            '[%s] Starting live game sync for season %d.',
            now()->toDateTimeString(),
            $season,
        );

        $this->info($startedMessage);
        Log::info($startedMessage, [
            'command' => 'import:live-games',
            'season' => $season,
        ]);

        if (empty($apiKey)) {
            $this->error('FOOTBALL_DATA_API_KEY is not set in your .env file.');
            Log::error('Live game sync failed: missing FOOTBALL_DATA_API_KEY.', [
                'command' => 'import:live-games',
                'season' => $season,
            ]);
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
            Log::info('Live game sync skipped: no games currently in progress.', [
                'command' => 'import:live-games',
                'season' => $season,
            ]);
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
            Log::error('Live game sync failed: football-data API request failed.', [
                'command' => 'import:live-games',
                'season' => $season,
                'status' => $response->status(),
            ]);
            return self::FAILURE;
        }

        $allMatches = $response->json('matches', []);
        $liveApiIds = $liveGames->pluck('apiId')->toArray();

        $this->info('API returned ' . count($allMatches) . ' match(es) for this season.');

        // Filter to only the live games we care about
        $liveMatches = array_filter(
            $allMatches,
            fn ($m) => in_array($m['id'], $liveApiIds, true)
        );

        if (empty($liveMatches)) {
            $this->warn('Live games not found in API response.');
            Log::warning('Live game sync found no matching live games in API response.', [
                'command' => 'import:live-games',
                'season' => $season,
                'live_api_ids' => $liveApiIds,
            ]);
            return self::SUCCESS;
        }

        $this->info("Updating " . count($liveMatches) . " live game(s)...");
        $standingIdsToRecalculate = [];
        $updatedGames = 0;
        $unchangedGames = 0;

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

                $this->line("  Updated [match {$game->apiId}]: {$game->homeTeam->shortName} {$scoreHome} - {$scoreAway} {$game->awayTeam->shortName}");
                $updatedGames++;
            } else {
                if ($logUnchanged) {
                    $this->line("  Unchanged [match {$game->apiId}]: {$game->homeTeam->shortName} vs {$game->awayTeam->shortName}");
                }
                $unchangedGames++;
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

        $duration = round(microtime(true) - $startedAt, 2);

        $this->info("Summary: {$updatedGames} updated, {$unchangedGames} unchanged.");
        $this->info('Duration: ' . number_format($duration, 2) . 's.');
        $this->info('Live games synced successfully.');

        Log::info('Live game sync completed successfully.', [
            'command' => 'import:live-games',
            'season' => $season,
            'live_games_found' => $liveGames->count(),
            'live_matches_processed' => count($liveMatches),
            'updated_games' => $updatedGames,
            'unchanged_games' => $unchangedGames,
            'recalculated_standing_ids' => array_keys($standingIdsToRecalculate),
            'duration_seconds' => $duration,
        ]);

        return self::SUCCESS;
    }
}
