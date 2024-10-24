<?php

declare(strict_types=1);

namespace Gatherling\Views;

class InfobotError extends InfobotResponse
{
    public function template(): string
    {
        return 'infobotError.mustache';
    }
}
