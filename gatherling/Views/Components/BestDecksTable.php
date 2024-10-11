<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;

class BestDecksTable extends Component
{
    public string $playerName;
    /** @var array<array{name: string, link: string, record: string, medals: array<array{src: string, alt: string}>}> */
    public array $decks = [];

    public function __construct(Player $player)
    {
        $this->playerName = $player->name ?? '';
        foreach ($player->getBestDeckStats() as $row) {
            if ($row['score'] <= 0) {
                continue;
            }
            $name = $row['name'];
            if (rtrim($name) == '') {
                $name = '* NO NAME *';
            }
            $link = 'deck.php?mode=view&id=' . rawurlencode((string) $row['id']);
            $record = deckRecordString($row['name'], $player);

            $medals = [];
            foreach (['1st', '2nd', 't4', 't8'] as $medal) {
                for ($i = 0; $i < $row[$medal]; $i++) {
                    $medals[] = ['src' => "styles/images/{$medal}.png", 'alt' => $medal];
                }
            }
            $this->decks[] = [
                'name' => $name,
                'link' => $link,
                'record' => $record,
                'medals' => $medals,
            ];
        }
    }
}

function deckRecordString(string $deckname, Player $player): string
{
    $matches = $player->getMatchesByDeckName($deckname);
    $wins = 0;
    $losses = 0;
    $draws = 0;

    foreach ($matches as $match) {
        if ($match->playerWon($player->name)) {
            $wins++;
        } elseif ($match->playerLost($player->name)) {
            $losses++;
        } elseif ($match->playerBye($player->name)) {
            $wins = $wins + 1;
        } elseif ($match->playerMatchInProgress($player->name)) {
            // do nothing since match is in progress and there are no results
        } else {
            $draws++;
        }
    }
    $recordString = $wins . '-' . $losses;
    if ($draws > 0) {
        $recordString .= '-' . $draws;
    }

    return $recordString;
}
