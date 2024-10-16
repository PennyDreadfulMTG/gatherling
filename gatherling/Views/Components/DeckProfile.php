<?php

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
    public ExactMatchTable $exactMatchTable;
    public CommentsTable $commentsTable;

    public function __construct(Deck $deck)
    {
        parent::__construct('partials/deckProfile');

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
        $this->exactMatchTable = new ExactMatchTable($deck);
        $this->commentsTable = new CommentsTable($deck);
        $this->canEdit = $deck->canEdit(Player::loginName());
    }
}
