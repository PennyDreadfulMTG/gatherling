<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;
use Gatherling\Models\Player;
use InvalidArgumentException;

class DeckProfile extends Component
{
    public int $deckId;
    public bool $canEdit;
    public DeckErrorTable $deckErrorTable;
    public DeckInfoCell $deckInfoCell;
    public MaindeckTable $maindeckTable;
    public SideboardTable $sideboardTable;
    public DeckTrophyCell $deckTrophyCell;
    public MatchupTable $matchupTable;
    public SymbolTable $symbolTable;
    public CcTable $ccTable;
    public ?ExactMatchTable $exactMatchTable = null;
    public CommentsTable $commentsTable;

    public function __construct(Deck $deck)
    {
        if (!$deck->id) {
            throw new InvalidArgumentException('Deck ID is required');
        }
        $this->deckId = $deck->id;
        if (!$deck->isValid()) {
            $deckErrors = $deck->getErrors();
            $this->deckErrorTable = new DeckErrorTable($deckErrors);
        }
        $this->deckInfoCell = new DeckInfoCell($deck);
        $this->maindeckTable = new MaindeckTable($deck);
        $this->sideboardTable = new SideboardTable($deck);
        $this->deckTrophyCell = new DeckTrophyCell($deck);
        $this->matchupTable = new MatchupTable($deck);
        $this->symbolTable = new SymbolTable($deck);
        $this->ccTable = new CcTable($deck);
        if ($deck->maindeck_cardcount >= 5) {
            $decks = $deck->findIdenticalDecks();
            if (count($decks) > 0) {
                $this->exactMatchTable = new ExactMatchTable($decks);
            }
        }
        $this->commentsTable = new CommentsTable($deck->notes ?? '');
        $this->canEdit = $deck->canEdit(Player::loginName());
    }
}
