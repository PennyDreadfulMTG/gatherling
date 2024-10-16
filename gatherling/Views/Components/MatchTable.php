<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;
use Gatherling\Exceptions\NotFoundException;

class MatchTable extends Component
{
    /** @var array<array{rnd: string, deckLink: ?DeckLink, opponentLink: PlayerLink, oppRating: int, res: string, playerWins: int, playerLosses: int, rowColor: string, eventName: string}> */
    public array $rows;

    public function __construct(Player $player, string $selectedFormat, string $selectedSeries, string $selectedSeason, string $selectedOpponent)
    {
        $matches = $player->getFilteredMatches($selectedFormat, $selectedSeries, $selectedSeason, $selectedOpponent);
        $oldName = '';
        $rowcolor = 'even';
        $Count = 1;
        foreach ($matches as $match) {
            $rnd = (string) ($match->round ?? 1);
            if ($match->timing == 2 && $match->type == 'Single Elimination') {
                $rnd = 'T' . pow(2, $match->rounds + 1 - $match->round);
            }

            if ($match->type == 'League') {
                $rnd = 'L';
            }

            $opp = $match->otherPlayer($player->name ?? '');
            if (!$opp) {
                throw new NotFoundException("Opponent not found for match {$match->id}");
            }
            $res = 'D';
            if ($match->playerWon($player->name ?? '')) {
                $res = 'W';
            }
            if ($match->playerLost($player->name ?? '')) {
                $res = 'L';
            }
            $opponent = new Player($opp);

            $event = $match->getEvent();
            if (!$event->id) {
                throw new NotFoundException("Event not found for match {$match->id}");
            }
            $oppRating = $opponent->getRating('Composite', $event->start ?? '');
            $oppDeck = $opponent->getDeckEvent($event->id);

            if ($oldName != $event->name) {
                if ($Count % 2 != 0) {
                    $rowcolor = 'odd';
                    $Count++;
                } else {
                    $rowcolor = 'even';
                    $Count++;
                }
                $eventName = $event->name ?? '';
            } else {
                $eventName = '';
            }
            $oldName = $event->name;
            $wins = $player->name ? $match->getPlayerWins($player->name) : 0;
            $losses = $player->name ? $match->getPlayerLosses($player->name) : 0;
            $this->rows[] = [
                'rnd' => $rnd,
                'deckLink' => $oppDeck ? new DeckLink($oppDeck) : null,
                'opponentLink' => new PlayerLink($opponent),
                'oppRating' => $oppRating,
                'res' => $res,
                'playerWins' => $wins ?: 0,
                'playerLosses' => $losses ?: 0,
                'rowColor' => $rowcolor,
                'eventName' => $eventName,
            ];
        }
    }
}
