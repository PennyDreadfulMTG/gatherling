<?php

declare(strict_types=1);

namespace Gatherling\Models;

class LegalCardDto extends Dto
{
    public string $original_name;
    public ?int $id;
    public ?string $name;
    public ?int $allowed;
}
