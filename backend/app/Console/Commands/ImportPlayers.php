<?php

namespace App\Console\Commands;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\QueryException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportPlayers extends Command
{
    protected $signature = 'import:players';

    protected $description = 'Import FIFA World Cup 2026 players from football-data.org';

    public function handle(): int
    {
        $startedAt = microtime(true);
        $apiKey = config('services.football_data.key');

        Log::info('Player import started.', [
            'command' => 'import:players',
        ]);

        if (empty($apiKey)) {
            $this->error('FOOTBALL_DATA_API_KEY is not set in your .env file.');
            Log::error('Player import failed: missing FOOTBALL_DATA_API_KEY.', [
                'command' => 'import:players',
            ]);
            return self::FAILURE;
        }

        $this->info('Fetching teams and squads from football-data.org...');

        $response = Http::withHeaders([
            'X-Auth-Token' => $apiKey,
        ])->get('https://api.football-data.org/v4/competitions/WC/teams');

        if ($response->failed()) {
            $this->error("API request failed: {$response->status()} {$response->body()}");
            Log::error('Player import failed: football-data API request failed.', [
                'command' => 'import:players',
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return self::FAILURE;
        }

        $teams = $response->json('teams', []);

        if (empty($teams)) {
            $this->warn('No teams returned from the API.');
            Log::warning('Player import completed: no teams returned by API.', [
                'command' => 'import:players',
            ]);
            return self::SUCCESS;
        }

        try {
            $teamCache = Team::whereNotNull('apiId')->get()->keyBy('apiId');
        } catch (QueryException $exception) {
            $duration = round(microtime(true) - $startedAt, 2);

            $this->error('Database connection/query failed while loading teams.');
            $this->warn('If DB_HOST is set to "postgres", run this command from inside the Docker app container, or change DB_HOST to a host-reachable value (for example 127.0.0.1) and clear config cache.');

            Log::error('Player import failed: database query exception while loading teams.', [
                'command' => 'import:players',
                'db_host' => config('database.connections.pgsql.host'),
                'db_database' => config('database.connections.pgsql.database'),
                'sql_state' => $exception->getCode(),
                'error' => $exception->getMessage(),
                'duration_seconds' => $duration,
            ]);

            return self::FAILURE;
        }

        $totalSquadEntries = array_sum(array_map(
            static fn (array $team): int => count($team['squad'] ?? []),
            $teams
        ));

        if ($totalSquadEntries === 0) {
            $this->warn('No squad entries found in the API response.');
            Log::warning('Player import completed: no squad entries returned by API.', [
                'command' => 'import:players',
                'teams_count' => count($teams),
            ]);
            return self::SUCCESS;
        }

        $this->info("Importing {$totalSquadEntries} player entries...");
        $bar = $this->output->createProgressBar($totalSquadEntries);
        $bar->start();

        $imported = 0;
        $skippedTeams = 0;
        $skippedPlayersWithoutApiId = 0;

        foreach ($teams as $teamData) {
            $teamApiId = $teamData['id'] ?? null;
            $team = $teamApiId ? $teamCache->get($teamApiId) : null;

            if (! $team) {
                $skippedTeams++;
                foreach (($teamData['squad'] ?? []) as $_) {
                    $bar->advance();
                }
                continue;
            }

            foreach (($teamData['squad'] ?? []) as $playerData) {
                $apiId = $playerData['id'] ?? null;

                if (! $apiId) {
                    $skippedPlayersWithoutApiId++;
                    $bar->advance();
                    continue;
                }

                $name = $this->normalizeText($playerData['name'] ?? null);
                $dateOfBirth = $this->normalizeDate($playerData['dateOfBirth'] ?? null);

                try {
                    Player::updateOrCreate(
                        ['apiId' => $apiId],
                        [
                            'name' => $name,
                            'dateOfBirth' => $dateOfBirth,
                            'role' => $this->normalizeText($playerData['position'] ?? null),
                            'teamId' => $team->id,
                        ]
                    );
                } catch (QueryException $exception) {
                    $duration = round(microtime(true) - $startedAt, 2);

                    $bar->finish();
                    $this->newLine();
                    $this->error('Database write failed while importing players.');
                    $this->error($exception->getMessage());

                    Log::error('Player import failed: database query exception while writing players.', [
                        'command' => 'import:players',
                        'db_host' => config('database.connections.pgsql.host'),
                        'db_database' => config('database.connections.pgsql.database'),
                        'db_driver' => config('database.default'),
                        'player_api_id' => $apiId,
                        'team_api_id' => $teamApiId,
                        'sql_state' => $exception->getCode(),
                        'sql' => $exception->getSql(),
                        'bindings' => $exception->getBindings(),
                        'error' => $exception->getMessage(),
                        'duration_seconds' => $duration,
                    ]);

                    return self::FAILURE;
                }

                $imported++;
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();

        $duration = round(microtime(true) - $startedAt, 2);

        $this->info("Players imported successfully: {$imported}");

        if ($skippedTeams > 0) {
            $this->warn("Skipped {$skippedTeams} team(s) because they do not exist in local DB. Run import:teams first.");
        }

        Log::info('Player import completed successfully.', [
            'command' => 'import:players',
            'teams_count' => count($teams),
            'total_squad_entries' => $totalSquadEntries,
            'imported_players' => $imported,
            'skipped_teams' => $skippedTeams,
            'skipped_players_without_api_id' => $skippedPlayersWithoutApiId,
            'duration_seconds' => $duration,
        ]);

        return self::SUCCESS;
    }

    private function normalizeText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        if ($text === '') {
            return null;
        }

        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (! mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
        }

        $sanitized = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
        if ($sanitized !== false) {
            $text = $sanitized;
        }

        $text = preg_replace('/[[:cntrl:]]/u', '', $text) ?? $text;

        return $text === '' ? null : $text;
    }

    private function normalizeDate(mixed $value): ?string
    {
        $date = $this->normalizeText($value);

        if ($date === null) {
            return null;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 ? $date : null;
    }
}
