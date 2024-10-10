<?php

declare(strict_types=1);

namespace Gatherling\Models;

class ImageDto extends Dto
{
    public ?string $image;
    public ?string $type;
    public ?int $size;
}
