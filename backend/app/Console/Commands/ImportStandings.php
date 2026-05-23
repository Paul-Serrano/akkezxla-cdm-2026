<?php

namespace App\Console\Commands;

use App\Models\Standing;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportStandings extends Command
{
    protected $signature = 'import:standings {--season=2026 : Season to import standings for (e.g. 2026)}';

    protected $description = 'Import FIFA World Cup standings from football-data.org into team standing fields';

    public function handle(): int
    {
        $startedAt = microtime(true);
        $apiKey = config('services.football_data.key');
        $season = (int) $this->option('season');

        Log::info('Standings import started.', [
            'command' => 'import:standings',
            'season' => $season,
        ]);

        if (empty($apiKey)) {
            $this->error('FOOTBALL_DATA_API_KEY is not set in your .env file.');
            Log::error('Standings import failed: missing FOOTBALL_DATA_API_KEY.', [
                'command' => 'import:standings',
                'season' => $season,
            ]);
            return self::FAILURE;
        }

        $this->info('Fetching standings from football-data.org...');

        $response = Http::withHeaders([
            'X-Auth-Token' => $apiKey,
        ])->get('https://api.football-data.org/v4/competitions/WC/standings', [
            'season' => $season,
        ]);

        if ($response->failed()) {
            $this->error("API request failed: {$response->status()} {$response->body()}");
            Log::error('Standings import failed: football-data API request failed.', [
                'command' => 'import:standings',
                'season' => $season,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return self::FAILURE;
        }

        $standings = $response->json('standings', []);

        // Some API versions return a flattened GROUP_STAGE table when season is provided.
        // If that happens, fall back to grouped standings (no season query) to keep Group A/B ordering.
        $fallbackToGrouped = false;
        if ($this->isUngroupedSeasonShape($standings)) {
            $this->warn('Season-scoped standings response is ungrouped; retrying with grouped standings endpoint...');
            Log::warning('Standings import detected ungrouped season response; retrying without season query.', [
                'command' => 'import:standings',
                'season' => $season,
            ]);

            $fallbackResponse = Http::withHeaders([
                'X-Auth-Token' => $apiKey,
            ])->get('https://api.football-data.org/v4/competitions/WC/standings');

            if ($fallbackResponse->failed()) {
                $this->error("Fallback API request failed: {$fallbackResponse->status()} {$fallbackResponse->body()}");
                Log::error('Standings import fallback request failed.', [
                    'command' => 'import:standings',
                    'season' => $season,
                    'status' => $fallbackResponse->status(),
                    'body' => $fallbackResponse->body(),
                ]);

                return self::FAILURE;
            }

            $standings = $fallbackResponse->json('standings', []);
            $fallbackToGrouped = true;
        }

        if (empty($standings)) {
            $this->warn('No standings returned from the API.');
            Log::warning('Standings import completed: no standings returned by API.', [
                'command' => 'import:standings',
                'season' => $season,
            ]);
            return self::SUCCESS;
        }

        $rawBlocksCount = count($standings);

        // The endpoint can return TOTAL/HOME/AWAY tables; we import only TOTAL to match the explorer panel.
        $standings = array_values(array_filter($standings, static function (array $block): bool {
            $type = strtoupper((string) ($block['type'] ?? ''));

            return $type === 'TOTAL';
        }));

        if (empty($standings)) {
            $this->warn('No TOTAL standings blocks found; nothing imported.');
            Log::warning('Standings import completed: no TOTAL standings blocks found.', [
                'command' => 'import:standings',
                'season' => $season,
                'raw_standings_blocks' => $rawBlocksCount,
            ]);
            return self::SUCCESS;
        }

        $teamCache = Team::whereNotNull('apiId')->get()->keyBy('apiId');

        $totalRows = 0;
        foreach ($standings as $block) {
            $totalRows += count($block['table'] ?? []);
        }

        if ($totalRows === 0) {
            $this->warn('No standing rows to import.');
            Log::warning('Standings import completed: no standing rows in response.', [
                'command' => 'import:standings',
                'season' => $season,
                'standings_blocks' => count($standings),
            ]);
            return self::SUCCESS;
        }

        $this->info("Importing {$totalRows} standing rows...");
        $bar = $this->output->createProgressBar($totalRows);
        $bar->start();

        $updated = 0;
        $missingTeams = 0;
        foreach ($standings as $block) {
            $groupRaw = (string) ($block['group'] ?? '');
            $groupName = $this->normalizeGroupName($groupRaw);
            $standing = $groupName !== ''
                ? Standing::firstOrCreate(['name' => $groupName], ['apiId' => null])
                : null;

            foreach (($block['table'] ?? []) as $row) {
                $teamApiId = $row['team']['id'] ?? null;
                $team = $teamApiId ? $teamCache->get($teamApiId) : null;

                if (! $team) {
                    $missingTeams++;
                    $bar->advance();
                    continue;
                }

                if ($standing && $team->standingId !== $standing->id) {
                    $team->standingId = $standing->id;
                }

                $team->rank = $row['position'] ?? $team->rank;
                $team->standingPosition = $row['position'] ?? null;
                $team->standingPlayedGames = $row['playedGames'] ?? null;
                $team->standingForm = isset($row['form']) ? (string) $row['form'] : null;
                $team->standingWon = $row['won'] ?? null;
                $team->standingDraw = $row['draw'] ?? null;
                $team->standingLost = $row['lost'] ?? null;
                $team->standingPoints = $row['points'] ?? null;
                $team->standingGoalsFor = $row['goalsFor'] ?? null;
                $team->standingGoalsAgainst = $row['goalsAgainst'] ?? null;
                $team->standingGoalDifference = $row['goalDifference'] ?? null;

                if (!empty($row['team']['name'])) {
                    $team->name = (string) $row['team']['name'];
                }
                if (!empty($row['team']['shortName'])) {
                    $team->shortName = (string) $row['team']['shortName'];
                }
                if (!empty($row['team']['crest'])) {
                    $team->crest = (string) $row['team']['crest'];
                }

                $team->save();
                $updated++;
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();

        $this->info("Standings imported successfully: {$updated} teams updated.");
        if ($missingTeams > 0) {
            $this->warn("Skipped {$missingTeams} row(s) because local teams were missing. Run import:teams first.");
        }

        $duration = round(microtime(true) - $startedAt, 2);

        Log::info('Standings import completed successfully.', [
            'command' => 'import:standings',
            'season' => $season,
            'used_grouped_fallback' => $fallbackToGrouped,
            'raw_standings_blocks' => $rawBlocksCount,
            'standings_blocks' => count($standings),
            'total_rows' => $totalRows,
            'updated_teams' => $updated,
            'missing_teams' => $missingTeams,
            'duration_seconds' => $duration,
        ]);

        return self::SUCCESS;
    }

    private function normalizeGroupName(string $group): string
    {
        $group = trim($group);

        if ($group === '') {
            return '';
        }

        if (stripos($group, 'group ') === 0) {
            return trim(substr($group, 6));
        }

        return $group;
    }

    private function isUngroupedSeasonShape(array $standings): bool
    {
        if ($standings === [] || !isset($standings[0]) || !is_array($standings[0])) {
            return false;
        }

        $first = $standings[0];
        $stage = strtoupper((string) ($first['stage'] ?? ''));
        $group = $first['group'] ?? null;

        return $stage === 'GROUP_STAGE' && ($group === null || trim((string) $group) === '');
    }
}
