<?php

declare(strict_types=1);

namespace Gatherling\Views;

class InfobotReply extends InfobotResponse
{
    public function template(): string
    {
        return 'infobotReply.mustache';
    }
}
