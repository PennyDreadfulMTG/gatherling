<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use PHPIcons\PHPIcons;

class Icon extends Component
{
    public string $iconSafe;

    public function __construct(string $code)
    {
        $phpicons = new PHPIcons(__DIR__ . '/../../../php-icons.php');
        $this->iconSafe = (string) $phpicons->icon($code);
    }
}
