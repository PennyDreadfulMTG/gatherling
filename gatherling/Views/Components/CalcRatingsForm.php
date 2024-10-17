<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Views\Components\FormatDropMenuR;

class CalcRatingsForm extends Component
{
    public FormatDropMenuR $formatDropMenuR;

    public function __construct()
    {
        $this->formatDropMenuR = new FormatDropMenuR();
    }
}
