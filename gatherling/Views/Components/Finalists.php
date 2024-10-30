<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;
use Gatherling\Models\Player;

class Finalists extends Component
{
    public int $numFinalists;
    /** @var list<array{medalSrc: string, medalText: string, manaSrc: ?string, deckLink: ?DeckLink, deckIsValid: bool, playerLink: PlayerLink}> */
    public array $finalists;

    /** @param array<array{medal: string, player: string, deck: ?int}> $finalists */
    public function __construct(array $finalists)
    {
        $this->numFinalists = count($finalists);
        $this->finalists = [];

        foreach ($finalists as $finalist) {
            $player = new Player($finalist['player']);
            $deck = $finalist['deck'] !== null ? new Deck($finalist['deck']) : null;

            $medalText = $finalist['medal'];
            if ($finalist['medal'] == 't8' || $finalist['medal'] == 't4') {
                $medalText = strtoupper($medalText);
            }

            $this->finalists[] = [
                'medalSrc' => 'styles/images/' . rawurlencode($finalist['medal']) . '.png',
                'medalText' => $medalText,
                'manaSrc' => $deck?->manaSrc(),
                'deckLink' => $deck ? new DeckLink($deck) : null,
                'deckIsValid' => $deck?->isValid() ?? false,
                'playerLink' => new PlayerLink($player),
            ];
        }
    }
}
