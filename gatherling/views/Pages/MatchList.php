<?php

namespace Gatherling\Views\Pages;

use Gatherling\Models\Event;
use Gatherling\Models\Matchup;
use Gatherling\Models\Player;

class MatchList extends EventFrame
{
    public array $roundLinks;
    public bool $hasMatches;
    public array $rounds;
    public array $lastRound;
    public array $playerADropMenu;
    public array $playerBDropMenu;
    public ?array $playerByeMenu;
    public ?array $roundDropMenu;
    public ?array $resultDropMenu;
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
                'link' => 'event.php?view=match&name='.rawurlencode($event->name)."#round-{$n}",
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
            $matchInfo['gameNameA'] = $playerA->gameNameArgs($event->client);
            $matchInfo['gameNameB'] = $playerB->gameNameArgs($event->client);

            $isActiveUnverified = strcasecmp($match->verification, 'verified') != 0 && $event->finalized == 0;
            if ($isActiveUnverified) {
                $matchInfo['unverifiedPlayerCellA'] = unverifiedPlayerCellArgs($event, $match, $playerA);
                $matchInfo['resultDropMenu'] = resultDropMenuArgs('matchresult[]');
                $matchInfo['unverifiedPlayerCellB'] = unverifiedPlayerCellArgs($event, $match, $playerB);
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

        $playerADropMenu = EventHelper::playerDropMenuArgs($event, 'A');
        $playerBDropMenu = EventHelper::playerDropMenuArgs($event, 'B');
        $playerByeMenu = $roundDropMenu = $resultDropMenu = null;
        if ($event->active) {
            $playerByeMenu = playerByeMenuArgs($event);
        } else {
            $roundDropMenu = EventHelper::roundDropMenuArgs($event, $newMatchRound);
            $resultDropMenu = resultDropMenuArgs('newmatchresult');
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

function playerByeMenuArgs(Event $event): array
{
    $playerNames = $event->getRegisteredPlayers(true);
    $options = [];
    foreach ($playerNames as $player) {
        $options[] = [
            'value' => $player,
            'text'  => $player,
        ];
    }

    return [
        'name'    => 'newbyeplayer',
        'default' => '- Bye Player -',
        'options' => $options,
    ];
}

function resultDropMenuArgs(string $name, array $extraOptions = []): array
{
    $options = [
        ['value' => '2-0', 'text' => '2-0'],
        ['value' => '2-1', 'text' => '2-1'],
        ['value' => '1-2', 'text' => '1-2'],
        ['value' => '0-2', 'text' => '0-2'],
        ['value' => 'D', 'text' => 'Draw'],

    ];
    foreach ($extraOptions as $value => $text) {
        $options[] = ['value' => $value, 'text' => $text];
    }

    return [
        'name'    => $name,
        'default' => '- Result -',
        'options' => $options,
    ];
}

function unverifiedPlayerCellArgs(Event $event, Matchup $match, Player $player): array
{
    $playerName = $player->name;
    $wins = $match->getPlayerWins($playerName);
    $losses = $match->getPlayerLosses($playerName);
    $matchResult = ($wins + $losses > 0) ? ($wins > $losses ? 'W' : 'L') : null;

    return [
        'playerName'      => $playerName,
        'displayName'     => $player->gameNameArgs($event->client),
        'displayNameText' => $player->gameName($event->client, false),
        'hasDropped'      => $match->playerDropped($playerName),
        'hasGames'        => ($wins + $losses > 0),
        'matchResult'     => $matchResult,
        'isDraw'          => ($wins == 1 && $losses == 1),
        'verification'    => $match->verification,
        'wins'            => $wins,
        'losses'          => $losses,
    ];
}