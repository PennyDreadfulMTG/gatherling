<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Views\Components\GameName;
use Gatherling\Views\Components\RoundDropMenu;
use Gatherling\Views\Components\PlayerByeMenu;
use Gatherling\Views\Components\PlayerDropMenu;
use Gatherling\Views\Components\ResultDropMenu;
use Gatherling\Views\Components\UnverifiedPlayerCell;

use function Gatherling\Helpers\getObjectVarsCamelCase;

class MatchList extends EventFrame
{
    /** @var list<array<string, string>> */
    public array $roundLinks;
    public bool $hasMatches;
    /** @var array<int, array<string, mixed>> */
    public array $rounds;
    /** @var array<string, mixed> */
    public array $lastRound;
    public PlayerDropMenu $playerADropMenu;
    public PlayerDropMenu $playerBDropMenu;
    public ?PlayerByeMenu $playerByeMenu;
    public ?RoundDropMenu $roundDropMenu;
    public ?ResultDropMenu $resultDropMenu;
    public bool $isBeforeRoundTwo;
    public string $structureSummary;
    public bool $isLeague;

    public function __construct(Event $event, string|int|null $newMatchRound)
    {
        parent::__construct($event);
        $matches = $event->getMatches();
        $roundLinks = [];
        for ($n = 1; $n <= $event->current_round; $n++) {
            $roundLinks[] = [
                'text' => "Round $n",
                'link' => 'event.php?view=match&name=' . rawurlencode($event->name) . "#round-{$n}",
            ];
        }
        $hasMatches = count($matches) > 0;
        $first = 1;
        $rndAdd = 0;
        $playersInMatches = [];
        $rounds = [];
        foreach ($matches as $match) {
            $matchInfo = getObjectVarsCamelCase($match);
            if ($first && $match->timing == 1) {
                $rndAdd = $match->rounds;
            }
            $first = 0;
            // add final round to main round if in extra rounds to keep round correct
            if ($match->timing == 2) {
                $printRnd = $match->round + $rndAdd;
            } else {
                $printRnd = $match->round;
            }
            $matchInfo['printRnd'] = $printRnd;
            $matchInfo['showStar'] = $match->timing > 1;
            if (!isset($rounds[$printRnd])) {
                $extraRoundTitle = '';
                if ($match->timing > 1) {
                    $extraRoundTitle = "(Finals Round {$match->round})";
                }
                $rounds[$printRnd] = ['round' => $printRnd, 'extraRoundTitle' => $extraRoundTitle, 'matches' => []];
            }

            if (!isset($playersInMatches[$match->playera])) {
                $playersInMatches[$match->playera] = new Player($match->playera);
            }
            if (!isset($playersInMatches[$match->playerb])) {
                $playersInMatches[$match->playerb] = new Player($match->playerb);
            }
            $playerA = $playersInMatches[$match->playera];
            $playerB = $playersInMatches[$match->playerb];
            $matchInfo['gameNameA'] = (new GameName($playerA, $event->client));
            $matchInfo['gameNameB'] = (new GameName($playerB, $event->client));

            $isActiveUnverified = strcasecmp($match->verification, 'verified') != 0 && $event->finalized == 0;
            if ($isActiveUnverified) {
                $matchInfo['unverifiedPlayerCellA'] = new UnverifiedPlayerCell($event, $match, $playerA);
                $matchInfo['resultDropMenu'] = new ResultDropMenu('matchresult[]');
                $matchInfo['unverifiedPlayerCellB'] = new UnverifiedPlayerCell($event, $match, $playerB);
            } else {
                $playerAWins = $match->getPlayerWins($match->playera);
                $playerBWins = $match->getPlayerWins($match->playerb);
                $matchInfo['playerAWins'] = $playerAWins;
                $matchInfo['playerBWins'] = $playerBWins;
                $matchInfo['hasPlayerADropped'] = $match->playerDropped($match->playera);
                $matchInfo['hasPlayerBDropped'] = $match->playerDropped($match->playerb);
                $isBye = $match->playera == $match->playerb;
                $isDraw = ($match->getPlayerWins($match->playera) == 1) && ($match->getPlayerWins($match->playerb) == 1);
                $matchInfo['hasResult'] = !$isBye && !$isDraw;
                $matchInfo['isBye'] = $isBye;
                $matchInfo['isDraw'] = $isDraw;
            }
            $matchInfo['isActiveUnverified'] = $isActiveUnverified;
            $rounds[$printRnd]['matches'][] = $matchInfo;
        }
        // 0-index $rounds for mustache, if they start at 1 it will fail to loop over them.
        $rounds = array_values($rounds);

        $lastRound = $rounds ? $rounds[count($rounds) - 1] : [];

        $playerADropMenu = new PlayerDropMenu($event, 'A');
        $playerBDropMenu = new PlayerDropMenu($event, 'B');
        $playerByeMenu = $roundDropMenu = $resultDropMenu = null;
        if ($event->active) {
            $playerByeMenu = new PlayerByeMenu($event);
        } else {
            $roundDropMenu = new RoundDropMenu($event, $newMatchRound);
            $resultDropMenu = new ResultDropMenu('newmatchresult');
        }

        $structure = $event->current_round > $event->mainrounds ? $event->finalstruct : $event->mainstruct;
        $isLeague = $structure == 'League';

        $this->roundLinks = $roundLinks;
        $this->hasMatches = $hasMatches;
        $this->rounds = $rounds;
        $this->lastRound = $lastRound;
        $this->playerADropMenu = $playerADropMenu;
        $this->playerBDropMenu = $playerBDropMenu;
        $this->playerByeMenu = $playerByeMenu;
        $this->roundDropMenu = $roundDropMenu;
        $this->resultDropMenu = $resultDropMenu;
        $this->isBeforeRoundTwo = $event->current_round <= 1;
        $this->structureSummary = $event->structureSummary();
        $this->isLeague = $isLeague;
    }
}
