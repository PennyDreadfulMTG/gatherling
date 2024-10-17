<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;

class CommentsTable extends Component
{
    public string $notes = '';

    public function __construct(Deck $deck)
    {
        $notes = $deck->notes;
        if ($notes) {
            $notes = strip_tags($notes);
            $notes = preg_replace("/\n/", '<br />', $notes) ?? $notes;
            $notes = preg_replace("/\[b\]/", '<b>', $notes) ?? $notes;
            $notes = preg_replace("/\[\/b\]/", '</b>', $notes) ?? $notes;
            $notes = preg_replace("/\[i\]/", '<i>', $notes) ?? $notes;
            $notes = preg_replace("/\[\/i\]/", '</i>', $notes) ?? $notes;
        }
        $this->notes = $notes ?? '';
    }
}
