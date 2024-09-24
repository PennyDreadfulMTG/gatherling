<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

class InsufficientPermissions extends Page
{
    public string $errorMsg;
    public function __construct(bool $isOrganizer)
    {
        parent::__construct();
        http_response_code(403);
        if ($isOrganizer) {
            $this->errorMsg = "You're not authorized to edit this format! Access Restricted.";
        } else {
            $this->errorMsg = "You're not an Admin here on Gatherling.com! Access Restricted.";
        }
    }
}
