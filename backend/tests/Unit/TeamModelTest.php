<?php

namespace Tests\Unit;

use App\Models\Standing;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Team relationships
     */
    public function test_team_standing_relationship(): void
    {
        $standing = Standing::create([
            'name' => 'GROUP_D',
        ]);

        $team = Team::create([
            'apiId' => 100,
            'name' => 'Belgium',
            'shortName' => 'BEL',
            'founded' => now(),
            'crest' => 'https://example.com/bel.png',
            'standingId' => $standing->id,
        ]);

        $this->assertNotNull($team->standing);
        $this->assertEquals('GROUP_D', $team->standing->name);
    }

    /**
     * Test: Team fillable attributes
     */
    public function test_team_fillable_attributes(): void
    {
        $standing = Standing::create([
            'name' => 'GROUP_E',
        ]);

        $team = Team::create([
            'apiId' => 101,
            'name' => 'Netherlands',
            'shortName' => 'NED',
            'founded' => 1889,
            'crest' => 'https://example.com/ned.png',
            'rank' => 5,
            'standingId' => $standing->id,
        ]);

        $this->assertEquals(101, $team->apiId);
        $this->assertEquals('Netherlands', $team->name);
        $this->assertEquals('NED', $team->shortName);
        $this->assertEquals(1889, $team->founded);
        $this->assertEquals(5, $team->rank);
    }
}
