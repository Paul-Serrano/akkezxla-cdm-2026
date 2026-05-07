<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Standing;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncLiveGameScoresIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Full integration - multiple games, standings recalculation
     */
    public function test_complete_workflow_with_standings_recalculation(): void
    {
        // Setup: Create GROUP_A with 4 teams
        $standing = Standing::create([
            'name' => 'GROUP_A',
        ]);

        $france = Team::create([
            'apiId' => 1,
            'name' => 'France',
            'shortName' => 'FRA',
            'founded' => now(),
            'crest' => 'https://example.com/fra.png',
            'standingId' => $standing->id,
        ]);

        $germany = Team::create([
            'apiId' => 2,
            'name' => 'Germany',
            'shortName' => 'GER',
            'founded' => now(),
            'crest' => 'https://example.com/ger.png',
            'standingId' => $standing->id,
        ]);

        $netherlands = Team::create([
            'apiId' => 3,
            'name' => 'Netherlands',
            'shortName' => 'NED',
            'founded' => now(),
            'crest' => 'https://example.com/ned.png',
            'standingId' => $standing->id,
        ]);

        $italy = Team::create([
            'apiId' => 4,
            'name' => 'Italy',
            'shortName' => 'ITA',
            'founded' => now(),
            'crest' => 'https://example.com/ita.png',
            'standingId' => $standing->id,
        ]);

        // Create multiple games in progress
        $game1 = Game::create([
            'apiId' => 101,
            'startDate' => now()->subMinutes(60),
            'homeTeamId' => $france->id,
            'awayTeamId' => $germany->id,
            'scoreHome' => null,
            'scoreAway' => null,
        ]);

        $game2 = Game::create([
            'apiId' => 102,
            'startDate' => now()->subMinutes(30),
            'homeTeamId' => $netherlands->id,
            'awayTeamId' => $italy->id,
            'scoreHome' => null,
            'scoreAway' => null,
        ]);

        // Mock API response
        Http::fake([
            'api.football-data.org/*' => Http::response([
                'matches' => [
                    [
                        'id' => 101,
                        'status' => 'FINISHED',
                        'score' => [
                            'fullTime' => ['home' => 3, 'away' => 0],
                            'regularTime' => ['home' => 3, 'away' => 0],
                        ],
                    ],
                    [
                        'id' => 102,
                        'status' => 'FINISHED',
                        'score' => [
                            'fullTime' => ['home' => 2, 'away' => 2],
                            'regularTime' => ['home' => 2, 'away' => 2],
                        ],
                    ],
                ],
            ]),
        ]);

        $this->artisan('import:live-games --season=2026')
            ->expectsOutput("Found 2 game(s) in progress. Fetching updates...")
            ->expectsOutput('Updating 2 live game(s)...')
            ->assertExitCode(0);

        // Verify both games updated
        $game1->refresh();
        $this->assertEquals(3, $game1->scoreHome);
        $this->assertEquals(0, $game1->scoreAway);

        $game2->refresh();
        $this->assertEquals(2, $game2->scoreHome);
        $this->assertEquals(2, $game2->scoreAway);
    }

    /**
     * Test: Sequential updates (game scores change over time)
     */
    public function test_sequential_score_updates(): void
    {
        $standing = Standing::create([
            'name' => 'GROUP_B',
        ]);

        $team1 = Team::create([
            'apiId' => 5,
            'name' => 'Spain',
            'shortName' => 'ESP',
            'founded' => now(),
            'crest' => 'https://example.com/esp.png',
            'standingId' => $standing->id,
        ]);

        $team2 = Team::create([
            'apiId' => 6,
            'name' => 'Portugal',
            'shortName' => 'POR',
            'founded' => now(),
            'crest' => 'https://example.com/por.png',
            'standingId' => $standing->id,
        ]);

        $game = Game::create([
            'apiId' => 201,
            'startDate' => now()->subMinutes(45),
            'homeTeamId' => $team1->id,
            'awayTeamId' => $team2->id,
            'scoreHome' => null,
            'scoreAway' => null,
        ]);

        // Sync with final score: 2-1
        Http::fake([
            'api.football-data.org/*' => Http::response([
                'matches' => [
                    [
                        'id' => 201,
                        'status' => 'FINISHED',
                        'score' => ['fullTime' => ['home' => 2, 'away' => 1], 'regularTime' => ['home' => 2, 'away' => 1]],
                    ],
                ],
            ]),
        ]);

        $this->artisan('import:live-games --season=2026')
            ->expectsOutput('  Updated: ESP 2 - 1 POR')
            ->assertExitCode(0);

        $game->refresh();
        $this->assertEquals(2, $game->scoreHome);
        $this->assertEquals(1, $game->scoreAway);
    }

    /**
     * Test: Mix of finished and unfinished games
     */
    public function test_handles_finished_and_ongoing_games_together(): void
    {
        $standing = Standing::create([
            'name' => 'GROUP_C',
        ]);

        $team1 = Team::create([
            'apiId' => 7,
            'name' => 'Brazil',
            'shortName' => 'BRA',
            'founded' => now(),
            'crest' => 'https://example.com/bra.png',
            'standingId' => $standing->id,
        ]);

        $team2 = Team::create([
            'apiId' => 8,
            'name' => 'Argentina',
            'shortName' => 'ARG',
            'founded' => now(),
            'crest' => 'https://example.com/arg.png',
            'standingId' => $standing->id,
        ]);

        $team3 = Team::create([
            'apiId' => 9,
            'name' => 'Mexico',
            'shortName' => 'MEX',
            'founded' => now(),
            'crest' => 'https://example.com/mex.png',
            'standingId' => $standing->id,
        ]);

        $team4 = Team::create([
            'apiId' => 10,
            'name' => 'Uruguay',
            'shortName' => 'URU',
            'founded' => now(),
            'crest' => 'https://example.com/uru.png',
            'standingId' => $standing->id,
        ]);

        // Game 1: Finished
        $game1 = Game::create([
            'apiId' => 301,
            'startDate' => now()->subHours(3),
            'homeTeamId' => $team1->id,
            'awayTeamId' => $team2->id,
            'scoreHome' => null,
            'scoreAway' => null,
        ]);

        // Game 2: Still in progress
        $game2 = Game::create([
            'apiId' => 302,
            'startDate' => now()->subMinutes(30),
            'homeTeamId' => $team3->id,
            'awayTeamId' => $team4->id,
            'scoreHome' => null,
            'scoreAway' => null,
        ]);

        Http::fake([
            'api.football-data.org/*' => Http::response([
                'matches' => [
                    [
                        'id' => 301,
                        'status' => 'FINISHED',
                        'score' => ['fullTime' => ['home' => 2, 'away' => 1], 'regularTime' => ['home' => 2, 'away' => 1]],
                    ],
                    [
                        'id' => 302,
                        'status' => 'FINISHED',
                        'score' => ['fullTime' => ['home' => 1, 'away' => 0], 'regularTime' => ['home' => 1, 'away' => 0]],
                    ],
                ],
            ]),
        ]);

        $this->artisan('import:live-games --season=2026')
            ->expectsOutput("Found 2 game(s) in progress. Fetching updates...")
            ->assertExitCode(0);

        $game1->refresh();
        $this->assertEquals(2, $game1->scoreHome);
        $this->assertEquals(1, $game1->scoreAway);

        $game2->refresh();
        $this->assertEquals(1, $game2->scoreHome);
        $this->assertEquals(0, $game2->scoreAway);
    }

    /**
     * Test: Game started but before next sync
     */
    public function test_game_just_started(): void
    {
        $standing = Standing::create([
            'name' => 'GROUP_D',
        ]);

        $team1 = Team::create([
            'apiId' => 11,
            'name' => 'England',
            'shortName' => 'ENG',
            'founded' => now(),
            'crest' => 'https://example.com/eng.png',
            'standingId' => $standing->id,
        ]);

        $team2 = Team::create([
            'apiId' => 12,
            'name' => 'Scotland',
            'shortName' => 'SCO',
            'founded' => now(),
            'crest' => 'https://example.com/sco.png',
            'standingId' => $standing->id,
        ]);

        // Game started exactly now
        $game = Game::create([
            'apiId' => 401,
            'startDate' => now(),
            'homeTeamId' => $team1->id,
            'awayTeamId' => $team2->id,
            'scoreHome' => null,
            'scoreAway' => null,
        ]);

        Http::fake([
            'api.football-data.org/*' => Http::response([
                'matches' => [
                    [
                        'id' => 401,
                        'status' => 'IN_PLAY',
                        'score' => ['fullTime' => ['home' => 0, 'away' => 0], 'regularTime' => ['home' => 0, 'away' => 0]],
                    ],
                ],
            ]),
        ]);

        $this->artisan('import:live-games --season=2026')
            ->expectsOutput("Found 1 game(s) in progress. Fetching updates...")
            ->assertExitCode(0);

        $game->refresh();
        $this->assertEquals(0, $game->scoreHome);
        $this->assertEquals(0, $game->scoreAway);
    }

    /**
     * Test: Future game is not picked up
     */
    public function test_future_game_not_synced(): void
    {
        $standing = Standing::create([
            'name' => 'GROUP_E',
        ]);

        $team1 = Team::create([
            'apiId' => 13,
            'name' => 'Wales',
            'shortName' => 'WAL',
            'founded' => now(),
            'crest' => 'https://example.com/wal.png',
            'standingId' => $standing->id,
        ]);

        $team2 = Team::create([
            'apiId' => 14,
            'name' => 'Northern Ireland',
            'shortName' => 'NIR',
            'founded' => now(),
            'crest' => 'https://example.com/nir.png',
            'standingId' => $standing->id,
        ]);

        // Game starts in 1 hour
        $game = Game::create([
            'apiId' => 501,
            'startDate' => now()->addHour(),
            'homeTeamId' => $team1->id,
            'awayTeamId' => $team2->id,
            'scoreHome' => null,
            'scoreAway' => null,
        ]);

        Http::fake();

        $this->artisan('import:live-games --season=2026')
            ->expectsOutput('No games currently in progress.')
            ->assertExitCode(0);

        // Game should not be updated
        $game->refresh();
        $this->assertNull($game->scoreHome);
        $this->assertNull($game->scoreAway);
    }
}
