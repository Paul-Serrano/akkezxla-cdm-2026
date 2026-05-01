<?php

namespace Database\Seeders;

use App\Enums\BetResult;
use App\Models\Bet;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Ranking test seeder — admin only, never run in production.
 * Creates 6 fake users + fake finished games with varied bets.
 *
 * Run with:
 *   docker exec cdm_app php artisan db:seed --class=RankingTestSeeder
 *
 * Undo with:
 *   docker exec cdm_app php artisan db:seed --class=RankingTestSeeder --rollback
 */
class RankingTestSeeder extends Seeder
{
    /** Alias => [name, password] */
    private array $fakeUsers = [
        'Zizou'    => ['Zinedine Zidane',   'password'],
        'Ronaldo9' => ['Ronaldo Nazário',   'password'],
        'Cantona'  => ['Éric Cantona',      'password'],
        'Mbappé'   => ['Kylian Mbappé',     'password'],
        'Henry'    => ['Thierry Henry',     'password'],
        'Bergkamp' => ['Dennis Bergkamp',   'password'],
    ];

    public function run(): void
    {
        // Create fake users (winamax role so they appear in ranking)
        $users = [];
        foreach ($this->fakeUsers as $alias => $info) {
            $users[$alias] = User::firstOrCreate(
                ['email' => strtolower($alias) . '.test@cdm2026.local'],
                [
                    'name'     => $info[0],
                    'alias'    => $alias,
                    'password' => Hash::make($info[1]),
                    'role'     => User::ROLE_WINAMAX,
                ]
            );
        }

        // Pick the first 8 games and mark them as finished with plausible scores
        $scores = [
            [2, 1], [0, 0], [3, 2], [1, 1],
            [4, 0], [1, 2], [2, 2], [3, 1],
        ];

        $games = Game::orderBy('id')->take(count($scores))->get();

        foreach ($games as $i => $game) {
            $game->scoreHome = $scores[$i][0];
            $game->scoreAway = $scores[$i][1];
            $game->save();
        }

        // Bet matrix per user — [scoreHome, scoreAway] per game (null = no bet)
        // Designed to give varied super wins / wins / losses
        $betMatrix = [
            //         G0       G1       G2       G3       G4       G5       G6       G7
            'Zizou'    => [[2,1], [0,0], [3,2], [1,1], [4,0], [1,2], [2,2], [3,1]], // perfect
            'Ronaldo9' => [[2,1], [1,0], [2,1], [1,1], [3,0], [0,2], [1,1], [2,0]], // mixed
            'Cantona'  => [[1,0], [0,1], [2,1], [0,0], [2,0], [1,2], [1,2], [3,1]], // some wins
            'Mbappé'   => [[2,1], [0,0], [1,0], [2,0], [4,0], [0,1], [2,2], [1,0]], // good
            'Henry'    => [[0,1], [1,1], [1,2], [2,1], [1,0], [2,1], [0,1], [1,2]], // mostly wrong
            'Bergkamp' => [[2,0], [0,0], null,  [1,1], [3,0], null,  [2,2], [2,1]], // few bets
        ];

        foreach ($betMatrix as $alias => $bets) {
            $user = $users[$alias];
            foreach ($bets as $i => $bet) {
                if ($bet === null || !isset($games[$i])) {
                    continue;
                }
                $game = $games[$i];
                Bet::updateOrCreate(
                    ['gameId' => $game->id, 'userId' => $user->id],
                    [
                        'scoreHome' => $bet[0],
                        'scoreAway' => $bet[1],
                        'bet'       => $bet[0] . '-' . $bet[1],
                        'playerId'  => null,
                    ]
                );
            }
        }

        $this->command->info('RankingTestSeeder: 6 users + 8 finished games + bets seeded.');
    }
}
