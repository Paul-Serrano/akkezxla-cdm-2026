<?php

namespace Tests\Unit;

use App\Models\Standing;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StandingModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Standing creation
     */
    public function test_standing_creation(): void
    {
        $standing = Standing::create([
            'name' => 'GROUP_F',
        ]);

        $this->assertNotNull($standing->id);
        $this->assertEquals('GROUP_F', $standing->name);
    }

    /**
     * Test: Standing has teams
     */
    public function test_standing_has_multiple_teams(): void
    {
        $standing = Standing::create([
            'name' => 'GROUP_G',
        ]);

        for ($i = 1; $i <= 4; $i++) {
            Team::create([
                'apiId' => $i,
                'name' => "Team $i",
                'shortName' => "T$i",
                'founded' => now(),
                'crest' => 'https://example.com/t' . $i . '.png',
                'standingId' => $standing->id,
            ]);
        }

        $this->assertCount(4, $standing->teams);
    }
}
