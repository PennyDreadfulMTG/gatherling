<?php

declare(strict_types=1);

namespace Gatherling\Views;

use Gatherling\Models\Image;

class ImageResponse extends Response
{
    public function __construct(private Image $image)
    {
        $this->setHeader('Content-length', (string) $image->size);
        $this->setHeader('Content-type', $image->type);
    }

    public function body(): string
    {
        return $this->image->content;
    }
}
