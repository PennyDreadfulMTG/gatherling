<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Decksearch;
use Zebra_Pagination as Pagination;

class DisplayDecks extends Component
{
    /** @var list<array{isEven: bool, playerLink: string, playerName: string, deckLink: string, deckName: string, archetype: string, format: string, created: ?Time, record: string}> */
    public array $decks = [];
    public string $paginationSafe = '';

    /** @param list<int> $deckIds */
    public function __construct(public array $deckIds)
    {
        parent::__construct('partials/displayDecks');
        $decksearch = new Decksearch();
        $ids_populated = $decksearch->idsToSortedInfo($deckIds);

        $records_per_page = 25;

        $pagination = new Pagination();
        $pagination->records(count($ids_populated));
        $pagination->records_per_page($records_per_page);
        $pagination->avoid_duplicate_content(false);

        //get the ids for the current page
        $ids_populated = array_slice($ids_populated, (($pagination->get_page() - 1) * $records_per_page), $records_per_page);

        $now = time();
        foreach ($ids_populated as $index => $deckinfo) {
            if (strlen($deckinfo['name']) > 23) {
                $deckinfo['name'] = preg_replace('/\s+?(\S+)?$/', '', substr($deckinfo['name'], 0, 22)) . '...';
            }
            $created = $deckinfo['created_date'] ? strtotime($deckinfo['created_date']) : null;
            $createdTime = $created ? new Time($created, $now) : null;
            $this->decks[] = [
                'isEven' => $index % 2 === 0,
                'playerLink' => 'profile.php?player=' . rawurlencode($deckinfo['playername']) . '&mode=Lookup+Profile',
                'playerName' => $deckinfo['playername'],
                'deckLink' => 'deck.php?mode=view&id=' . rawurlencode((string) $deckinfo['id']),
                'deckName' => $deckinfo['name'],
                'archetype' => $deckinfo['archetype'],
                'format' => $deckinfo['format'],
                'created' => $createdTime,
                'record' => $deckinfo['record'],
            ];
        }

        ob_start();
        $pagination->render();
        $this->paginationSafe = ob_get_clean() ?: '';
    }
}
