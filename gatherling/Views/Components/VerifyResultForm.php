<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class VerifyResultForm extends Component
{
    public bool $showOpponent;
    public string $resultText = '';
    public bool $isDrop;

    public function __construct(public string $report, public int $matchId, public string $playerName, public string|int $drop, public string $opponent, public string $eventName)
    {
        parent::__construct('partials/verifyResultForm');
        $this->showOpponent = $opponent != '0';
        switch ($report) {
            case 'W20':
                $this->resultText = 'I won the match 2-0';
                break;
            case 'W21':
                $this->resultText = 'I won the match 2-1';
                break;
            case 'L20':
                $this->resultText = 'I lost the match 0-2';
                break;
            case 'L21':
                $this->resultText = 'I lost the match 1-2';
                break;
            case 'D':
                $this->resultText = 'The match was a draw';
                break;
        }
        $this->isDrop = $drop == 1 || $drop == 'Y';
    }
}
