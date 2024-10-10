<?php

declare(strict_types=1);

namespace Gatherling\Models;

use Bakert\BlossomPhp\MaxWeightMatching;

class Pairings
{
    private int $lowestScoreWithoutBye = -1;
    private int $highest_points = 0;
    private string $byeName = '';
    /** @var array<int, int> */
    public array $pairing = [];

    /**
     * @param array<int, array<string, int|string|array<int, string>>> $players
     * @param array<string, int|string|array<int, string>> $bye_data
     */
    public function __construct(array $players, array $bye_data)
    {
        // $highest_points = 0;
        $byeExist = (count($bye_data) > 0);
        for ($i = 0; $i < count($players); $i++) {
            $this->highest_points = max($this->highest_points, (int) $players[$i]['score']);
            if ($byeExist) {
                $this->byeName = $bye_data['player'];
                if (!in_array($this->byeName, $players[$i]['opponents'])) {
                    if ($this->lowestScoreWithoutBye < 0) {
                        $this->lowestScoreWithoutBye = (int) $players[$i]['score'];
                    } else {
                        $this->lowestScoreWithoutBye = min($this->lowestScoreWithoutBye, (int) $players[$i]['score']);
                    }
                }
            }
        }

        if ($byeExist) {
            //In a weird case where all players have had at least 1 bye
            $this->lowestScoreWithoutBye = max($this->lowestScoreWithoutBye, 0);
            // $players[$this->indexOfBye]['score'] = $this->lowestScoreWithoutBye - 3;
            $bye_data['score'] = $this->lowestScoreWithoutBye;
            array_push($players, $bye_data);
        }

        $weights = $this->weights($players);
        $mweight = new MaxWeightMatching($weights);
        $this->pairing = $mweight->main();
    }

    /**
     * @param array<int, array<string, int|string|array<int, string>>> $players
     * @return list<array{int, int, int}>
     */
    public function weights(array $players): array
    {
        $weights = [];
        for ($i = 0; $i < count($players); $i++) {
            for ($j = 0; $j < count($players); $j++) {
                if ($i == $j) {
                    continue;
                }
                $new_data = [$i, $j, $this->weight($this->highest_points, $players[$i], $players[$j])];
                array_push($weights, $new_data);
            }
        }

        return $weights;
    }

    /**
     * @param array{score: int, player: string, opponents: array<int, string>} $player1
     * @param array{score: int, player: string, opponents: array<int, string>} $player2
     */
    public function weight(int $highest_points, array $player1, array $player2): int
    {
        $weight = 0;

        // A pairing where the participants have not played each other as many times as they have played at least one other participant outscore all pairings where the participants have played the most times.
        // This will stave off re-pairs and second byes for as long as possible, and then re-re-pairs and third byes, and so on …
        // $counter = count($player1['opponents']);

        if (!in_array($player2['player'], $player1['opponents'])) {
            $weight += $this->quality($highest_points, $highest_points) + 1;
            if ($player2['player'] == $this->byeName && $player1['score'] == $this->lowestScoreWithoutBye) {
                $weight += $this->quality($highest_points, $highest_points) + 1;
            }
            if ($player1['player'] == $this->byeName && $player2['score'] == $this->lowestScoreWithoutBye) {
                $weight += $this->quality($highest_points, $highest_points) + 1;
            }

            // Determine a score for the quality of this pairing based on the points of the higher scoring participant of the two (importance) and how close the two participant's records are.
            $best = max($player1['score'], $player2['score']);
            $worst = min($player1['score'], $player2['score']);
            $spread = $best - $worst;
            $closeness = $highest_points - $spread;
            $importance = $best;
            $weight += $this->quality($importance, $closeness);
        }

        return $weight;
    }

    public function quality(int $importance, int $closeness): int
    {
        // We add one to these values to avoid sometimes multiplying by zero and losing information.
        return pow($importance + 1, 2) * pow($closeness + 1, 2);
    }
}
