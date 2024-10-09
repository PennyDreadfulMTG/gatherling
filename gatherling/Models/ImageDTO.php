<?php

declare(strict_types=1);

namespace Gatherling\Models;

class ImageDTO extends DTO
{
    public string $image;
    public string $type;
    public int $size;
}
