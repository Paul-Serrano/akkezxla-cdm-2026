<?php

namespace App\Console\Commands;

use App\Models\Standing;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportTeams extends Command
{
    protected $signature = 'import:teams';

    protected $description = 'Import FIFA World Cup 2026 teams from football-data.org';

    public function handle(): int
    {
        $apiKey = config('services.football_data.key');

        if (empty($apiKey)) {
            $this->error('FOOTBALL_DATA_API_KEY is not set in your .env file.');
            return self::FAILURE;
        }

        $this->info('Fetching teams from football-data.org...');

        $response = Http::withHeaders([
            'X-Auth-Token' => $apiKey,
        ])->get('https://api.football-data.org/v4/competitions/WC/teams');

        if ($response->failed()) {
            $this->error("API request failed: {$response->status()} {$response->body()}");
            return self::FAILURE;
        }

        $teams = $response->json('teams', []);

        if (empty($teams)) {
            $this->warn('No teams returned from the API.');
            return self::SUCCESS;
        }

        $standing = Standing::firstOrCreate(
            ['apiId' => 2000],
            ['name' => 'FIFA World Cup']
        );

        $this->info('Importing ' . count($teams) . ' teams...');
        $bar = $this->output->createProgressBar(count($teams));
        $bar->start();

        foreach ($teams as $teamData) {
            $founded = null;
            if (!empty($teamData['founded'])) {
                $founded = $teamData['founded'] . '-01-01 00:00:00+00';
            } else {
                $founded = now()->toDateTimeString();
            }

            Team::updateOrCreate(
                ['apiId' => $teamData['id']],
                [
                    'name'       => $teamData['name'],
                    'shortName'  => $teamData['shortName'] ?? $teamData['name'],
                    'founded'    => $founded,
                    'crest'      => $teamData['crest'] ?? '',
                    'standingId' => $standing->id,
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Teams imported successfully.');

        return self::SUCCESS;
    }
}
