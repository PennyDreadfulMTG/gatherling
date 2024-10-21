<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;

class CommentsTable extends Component
{
    public string $notesSafe = '';

    public function __construct(string $notes)
    {
        $notes = strip_tags($notes);
        $notes = htmlspecialchars($notes, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $notes = preg_replace("/\n/", '<br />', $notes) ?? $notes;
        $notes = preg_replace("/\[b\]/", '<b>', $notes) ?? $notes;
        $notes = preg_replace("/\[\/b\]/", '</b>', $notes) ?? $notes;
        $notes = preg_replace("/\[i\]/", '<i>', $notes) ?? $notes;
        $notes = preg_replace("/\[\/i\]/", '</i>', $notes) ?? $notes;
        $this->notesSafe = $notes;
    }
}
