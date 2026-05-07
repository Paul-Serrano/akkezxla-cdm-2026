<?php

namespace Tests\Unit;

use App\Models\Game;
use App\Models\Standing;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Game relationships work correctly
     */
    public function test_game_relationships(): void
    {
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

        $game = Game::create([
            'apiId' => 999,
            'startDate' => now(),
            'homeTeamId' => $homeTeam->id,
            'awayTeamId' => $awayTeam->id,
            'scoreHome' => 1,
            'scoreAway' => 1,
        ]);

        $this->assertNotNull($game->homeTeam);
        $this->assertNotNull($game->awayTeam);
        $this->assertEquals('France', $game->homeTeam->name);
        $this->assertEquals('Germany', $game->awayTeam->name);
    }

    /**
     * Test: Game score update
     */
    public function test_game_score_update(): void
    {
        $standing = Standing::create([
            'name' => 'GROUP_B',
        ]);

        $team1 = Team::create([
            'apiId' => 3,
            'name' => 'Spain',
            'shortName' => 'ESP',
            'founded' => now(),
            'crest' => 'https://example.com/esp.png',
            'standingId' => $standing->id,
        ]);

        $team2 = Team::create([
            'apiId' => 4,
            'name' => 'Italy',
            'shortName' => 'ITA',
            'founded' => now(),
            'crest' => 'https://example.com/ita.png',
            'standingId' => $standing->id,
        ]);

        $game = Game::create([
            'apiId' => 888,
            'startDate' => now(),
            'homeTeamId' => $team1->id,
            'awayTeamId' => $team2->id,
            'scoreHome' => null,
            'scoreAway' => null,
        ]);

        $this->assertNull($game->scoreHome);
        $this->assertNull($game->scoreAway);

        // Update scores
        $game->update(['scoreHome' => 2, 'scoreAway' => 1]);
        $game->refresh();

        $this->assertEquals(2, $game->scoreHome);
        $this->assertEquals(1, $game->scoreAway);
    }

    /**
     * Test: Game with null scores
     */
    public function test_game_with_null_scores(): void
    {
        $standing = Standing::create([
            'name' => 'GROUP_C',
        ]);

        $team1 = Team::create([
            'apiId' => 5,
            'name' => 'Portugal',
            'shortName' => 'POR',
            'founded' => now(),
            'crest' => 'https://example.com/por.png',
            'standingId' => $standing->id,
        ]);

        $team2 = Team::create([
            'apiId' => 6,
            'name' => 'Poland',
            'shortName' => 'POL',
            'founded' => now(),
            'crest' => 'https://example.com/pol.png',
            'standingId' => $standing->id,
        ]);

        $game = Game::create([
            'apiId' => 777,
            'startDate' => now()->addHours(2),
            'homeTeamId' => $team1->id,
            'awayTeamId' => $team2->id,
            'scoreHome' => null,
            'scoreAway' => null,
        ]);

        $this->assertTrue(is_null($game->scoreHome) && is_null($game->scoreAway));
    }
}
