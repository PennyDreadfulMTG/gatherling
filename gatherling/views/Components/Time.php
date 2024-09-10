<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class Time extends Component
{
    public string $datetime;
    public string $text;

    public function __construct(int $time, int $now)
    {
        $this->datetime = date('c', $time);
        $this->text = human_date($time, $now);
        parent::__construct('partials/time');
    }
}
