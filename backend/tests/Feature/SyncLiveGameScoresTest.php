<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Standing;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncLiveGameScoresTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.football_data.key' => 'test-football-data-key']);
    }

    /**
     * Test: No games in progress → exits silently
     */
    public function test_exits_silently_when_no_games_in_progress(): void
    {
        $this->artisan('import:live-games --season=2026')
            ->expectsOutput('No games currently in progress.')
            ->assertExitCode(0);
    }

    /**
     * Test: Fetches and updates live game scores
     */
    public function test_updates_scores_for_live_games(): void
    {
        // Setup: Create teams and standing
        $standing = Standing::create([
            'name' => 'GROUP_A',
        ]);

        $homeTeam = Team::create([
            'apiId' => 1,
            'name' => 'France',
            'shortName' => 'FRA',
            'founded' => now(),
            'crest' => 'https://example.com/fra.png',
            'standingId' => $standing->id,
        ]);

        $awayTeam = Team::create([
            'apiId' => 2,
            'name' => 'Germany',
            'shortName' => 'GER',
            'founded' => now(),
            'crest' => 'https://example.com/ger.png',
            'standingId' => $standing->id,
        ]);

        // Create game in progress (started, no scores)
        $game = Game::create([
            'apiId' => 999,
            'startDate' => now()->subMinutes(30),
            'homeTeamId' => $homeTeam->id,
            'awayTeamId' => $awayTeam->id,
            'scoreHome' => null,
            'scoreAway' => null,
        ]);

        // Mock API response
        Http::fake([
            'api.football-data.org/*' => Http::response([
                'matches' => [
                    [
                        'id' => 999,
                        'status' => 'FINISHED',
                        'score' => [
                            'fullTime' => ['home' => 2, 'away' => 1],
                            'regularTime' => ['home' => 2, 'away' => 1],
                        ],
                    ],
                ],
            ]),
        ]);

        $this->artisan('import:live-games --season=2026')
            ->expectsOutput("Found 1 game(s) in progress. Fetching updates...")
            ->expectsOutput('Updating 1 live game(s)...')
            ->expectsOutput('  Updated [match 999]: FRA 2 - 1 GER')
            ->expectsOutput('Standings recalculated.')
            ->assertExitCode(0);

        // Assert scores were updated
        $game->refresh();
        $this->assertEquals(2, $game->scoreHome);
        $this->assertEquals(1, $game->scoreAway);
    }

    /**
     * Test: Does not update if scores haven't changed
     */
    public function test_skips_update_if_scores_unchanged(): void
    {
        $standing = Standing::create([
            'name' => 'GROUP_B',
        ]);

        $homeTeam = Team::create([
            'apiId' => 3,
            'name' => 'Spain',
            'shortName' => 'ESP',
            'founded' => now(),
            'crest' => 'https://example.com/esp.png',
            'standingId' => $standing->id,
        ]);

        $awayTeam = Team::create([
            'apiId' => 4,
            'name' => 'Italy',
            'shortName' => 'ITA',
            'founded' => now(),
            'crest' => 'https://example.com/ita.png',
            'standingId' => $standing->id,
        ]);

        // Game with existing scores
        $game = Game::create([
            'apiId' => 888,
            'startDate' => now()->subMinutes(20),
            'homeTeamId' => $homeTeam->id,
            'awayTeamId' => $awayTeam->id,
            'scoreHome' => 1,
            'scoreAway' => 1,
        ]);

        // API returns same scores
        Http::fake([
            'api.football-data.org/*' => Http::response([
                'matches' => [
                    [
                        'id' => 888,
                        'status' => 'IN_PLAY',
                        'score' => [
                            'fullTime' => ['home' => 1, 'away' => 1],
                            'regularTime' => ['home' => 1, 'away' => 1],
                        ],
                    ],
                ],
            ]),
        ]);

        $this->artisan('import:live-games --season=2026')
            ->assertExitCode(0);

        // Scores should remain unchanged
        $game->refresh();
        $this->assertEquals(1, $game->scoreHome);
        $this->assertEquals(1, $game->scoreAway);
    }

    /**
     * Test: Handles games not finished yet (scores null in API)
     */
    public function test_clears_scores_for_non_finished_games(): void
    {
        $standing = Standing::create([
            'name' => 'GROUP_C',
        ]);

        $homeTeam = Team::create([
            'apiId' => 5,
            'name' => 'Brazil',
            'shortName' => 'BRA',
            'founded' => now(),
            'crest' => 'https://example.com/bra.png',
            'standingId' => $standing->id,
        ]);

        $awayTeam = Team::create([
            'apiId' => 6,
            'name' => 'Argentina',
            'shortName' => 'ARG',
            'founded' => now(),
            'crest' => 'https://example.com/arg.png',
            'standingId' => $standing->id,
        ]);

        // Game started
        $game = Game::create([
            'apiId' => 777,
            'startDate' => now()->subMinutes(15),
            'homeTeamId' => $homeTeam->id,
            'awayTeamId' => $awayTeam->id,
            'scoreHome' => null,
            'scoreAway' => null,
        ]);

        // API returns game still in play (no scores)
        Http::fake([
            'api.football-data.org/*' => Http::response([
                'matches' => [
                    [
                        'id' => 777,
                        'status' => 'IN_PLAY',
                        'score' => [
                            'fullTime' => ['home' => null, 'away' => null],
                            'regularTime' => ['home' => null, 'away' => null],
                        ],
                    ],
                ],
            ]),
        ]);

        $this->artisan('import:live-games --season=2026')
            ->assertExitCode(0);

        // Scores should still be null
        $game->refresh();
        $this->assertNull($game->scoreHome);
        $this->assertNull($game->scoreAway);
    }

    /**
     * Test: Handles API failure gracefully
     */
    public function test_handles_api_failure(): void
    {
        $standing = Standing::create([
            'name' => 'GROUP_D',
        ]);

        $team1 = Team::create([
            'apiId' => 7,
            'name' => 'Team A',
            'shortName' => 'TEA',
            'founded' => now(),
            'crest' => 'https://example.com/tea.png',
            'standingId' => $standing->id,
        ]);

        $team2 = Team::create([
            'apiId' => 8,
            'name' => 'Team B',
            'shortName' => 'TEB',
            'founded' => now(),
            'crest' => 'https://example.com/teb.png',
            'standingId' => $standing->id,
        ]);

        Game::create([
            'apiId' => 666,
            'startDate' => now()->subMinutes(10),
            'homeTeamId' => $team1->id,
            'awayTeamId' => $team2->id,
            'scoreHome' => null,
            'scoreAway' => null,
        ]);

        // Mock API failure
        Http::fake([
            'api.football-data.org/*' => Http::response(null, 503),
        ]);

        $this->artisan('import:live-games --season=2026')
            ->expectsOutput('API request failed: 503')
            ->assertExitCode(1);
    }

    /**
     * Test: Updates multiple games simultaneously
     */
    public function test_updates_multiple_live_games(): void
    {
        $standing = Standing::create([
            'name' => 'GROUP_E',
        ]);

        // Create 3 games in progress
        $games = [];
        for ($i = 1; $i <= 3; $i++) {
            $home = Team::create([
                'apiId' => 100 + $i,
                'name' => "Team $i Home",
                'shortName' => "TH$i",
                'founded' => now(),
                'crest' => 'https://example.com/th' . $i . '.png',
                'standingId' => $standing->id,
            ]);

            $away = Team::create([
                'apiId' => 200 + $i,
                'name' => "Team $i Away",
                'shortName' => "TA$i",
                'founded' => now(),
                'crest' => 'https://example.com/ta' . $i . '.png',
                'standingId' => $standing->id,
            ]);

            $games[] = Game::create([
                'apiId' => 500 + $i,
                'startDate' => now()->subMinutes(20),
                'homeTeamId' => $home->id,
                'awayTeamId' => $away->id,
                'scoreHome' => null,
                'scoreAway' => null,
            ]);
        }

        // Mock API with 3 games
        Http::fake([
            'api.football-data.org/*' => Http::response([
                'matches' => [
                    [
                        'id' => 501,
                        'status' => 'FINISHED',
                        'score' => ['fullTime' => ['home' => 1, 'away' => 0], 'regularTime' => ['home' => 1, 'away' => 0]],
                    ],
                    [
                        'id' => 502,
                        'status' => 'FINISHED',
                        'score' => ['fullTime' => ['home' => 2, 'away' => 2], 'regularTime' => ['home' => 2, 'away' => 2]],
                    ],
                    [
                        'id' => 503,
                        'status' => 'FINISHED',
                        'score' => ['fullTime' => ['home' => 0, 'away' => 3], 'regularTime' => ['home' => 0, 'away' => 3]],
                    ],
                ],
            ]),
        ]);

        $this->artisan('import:live-games --season=2026')
            ->expectsOutput("Found 3 game(s) in progress. Fetching updates...")
            ->expectsOutput('Updating 3 live game(s)...')
            ->assertExitCode(0);

        // Verify all games updated
        $games[0]->refresh();
        $this->assertEquals(1, $games[0]->scoreHome);
        $this->assertEquals(0, $games[0]->scoreAway);

        $games[1]->refresh();
        $this->assertEquals(2, $games[1]->scoreHome);
        $this->assertEquals(2, $games[1]->scoreAway);

        $games[2]->refresh();
        $this->assertEquals(0, $games[2]->scoreHome);
        $this->assertEquals(3, $games[2]->scoreAway);
    }

    /**
     * Test: Ignores games from API that aren't in our database
     */
    public function test_ignores_games_not_in_database(): void
    {
        $standing = Standing::create([
            'name' => 'GROUP_F',
        ]);

        $team1 = Team::create([
            'apiId' => 9,
            'name' => 'Team X',
            'shortName' => 'TEX',
            'founded' => now(),
            'crest' => 'https://example.com/tex.png',
            'standingId' => $standing->id,
        ]);

        $team2 = Team::create([
            'apiId' => 10,
            'name' => 'Team Y',
            'shortName' => 'TEY',
            'founded' => now(),
            'crest' => 'https://example.com/tey.png',
            'standingId' => $standing->id,
        ]);

        // Create one game we care about
        $game = Game::create([
            'apiId' => 400,
            'startDate' => now()->subMinutes(5),
            'homeTeamId' => $team1->id,
            'awayTeamId' => $team2->id,
            'scoreHome' => null,
            'scoreAway' => null,
        ]);

        // API returns multiple games, including ones we don't have
        Http::fake([
            'api.football-data.org/*' => Http::response([
                'matches' => [
                    [
                        'id' => 400,
                        'status' => 'FINISHED',
                        'score' => ['fullTime' => ['home' => 2, 'away' => 0], 'regularTime' => ['home' => 2, 'away' => 0]],
                    ],
                    [
                        'id' => 999999,
                        'status' => 'FINISHED',
                        'score' => ['fullTime' => ['home' => 5, 'away' => 1], 'regularTime' => ['home' => 5, 'away' => 1]],
                    ],
                ],
            ]),
        ]);

        $this->artisan('import:live-games --season=2026')
            ->assertExitCode(0);

        // Only our game should be updated
        $game->refresh();
        $this->assertEquals(2, $game->scoreHome);
        $this->assertEquals(0, $game->scoreAway);
    }

    /**
     * Test: Respects API key requirement
     */
    public function test_fails_without_api_key(): void
    {
        config(['services.football_data.key' => '']);

        $this->artisan('import:live-games --season=2026')
            ->expectsOutput('FOOTBALL_DATA_API_KEY is not set in your .env file.')
            ->assertExitCode(1);
    }
}
