<?php

namespace Tests\Feature;

use App\Enums\GameStage;
use App\Models\Game;
use App\Models\Standing;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MatchDayPaginationTest extends TestCase
{
    use RefreshDatabase;

    private int $teamIndex = 1;
    private int $apiGameIndex = 1000;

    public function test_it_applies_stage_based_pagination_rules(): void
    {
        $standing = Standing::create([
            'name' => 'GROUP_A',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-06-01 12:00:00'));

        for ($i = 1; $i <= 8; $i++) {
            $this->createGame($standing->id, GameStage::GroupStage, 'GS' . $i, $i);
        }

        for ($i = 1; $i <= 7; $i++) {
            $this->createGame($standing->id, GameStage::Last32, 'L32' . $i, 20 + $i);
        }

        for ($i = 1; $i <= 8; $i++) {
            $this->createGame($standing->id, GameStage::Last16, 'L16' . $i, 40 + $i);
        }

        for ($i = 1; $i <= 4; $i++) {
            $this->createGame($standing->id, GameStage::QuarterFinals, 'QF' . $i, 60 + $i);
        }

        for ($i = 1; $i <= 2; $i++) {
            $this->createGame($standing->id, GameStage::SemiFinals, 'SF' . $i, 70 + $i);
        }

        $this->createGame($standing->id, GameStage::ThirdPlace, 'TP1', 80);
        $this->createGame($standing->id, GameStage::Final, 'F1', 81);

        // Page 3 should be first LAST_32 page and contain exactly one LAST_32 game.
        $this->get('/matchday/3')
            ->assertOk()
            ->assertSee('Match Page 3')
            ->assertSee('L321-H')
            ->assertSee('L321-A')
            ->assertDontSee('L322-H')
            ->assertDontSee('L322-A');

        // Page 4 should contain the next LAST_32 chunk (3 games).
        $this->get('/matchday/4')
            ->assertOk()
            ->assertSee('Match Page 4')
            ->assertSee('L322-H')
            ->assertSee('L324-H')
            ->assertDontSee('L321-H');

        // Last page should include both THIRD_PLACE and FINAL games.
        $this->get('/matchday/10')
            ->assertOk()
            ->assertSee('Match Page 10')
            ->assertSee('TP1-H')
            ->assertSee('F1-H');

        // Out-of-range page should clamp to the last page.
        $this->get('/matchday/999')
            ->assertOk()
            ->assertSee('Match Page 10')
            ->assertSee('TP1-H')
            ->assertSee('F1-H');
    }

    private function createGame(int $standingId, GameStage $stage, string $prefix, int $minutesOffset): void
    {
        $home = $this->createTeam($standingId, $prefix . '-H');
        $away = $this->createTeam($standingId, $prefix . '-A');

        Game::create([
            'apiId' => $this->apiGameIndex++,
            'startDate' => now()->addMinutes($minutesOffset),
            'scoreHome' => null,
            'scoreAway' => null,
            'homeTeamId' => $home->id,
            'awayTeamId' => $away->id,
            'stage' => $stage->value,
        ]);
    }

    private function createTeam(int $standingId, string $token): Team
    {
        return Team::create([
            'apiId' => $this->teamIndex++,
            'name' => $token,
            'shortName' => $token,
            'founded' => now(),
            'crest' => 'https://example.com/' . strtolower($token) . '.png',
            'standingId' => $standingId,
        ]);
    }
}
