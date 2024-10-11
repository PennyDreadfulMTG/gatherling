<?php

declare(strict_types=1);

namespace Gatherling\Models;

class Image
{
    public function __construct(public string $content, public string $type, public int $size)
    {
    }

    public static function fromValues(?ImageDto $values): self
    {
        if (!$values || !isset($values->image, $values->type, $values->size)) {
            return self::empty();
        }
        return new self($values->image, $values->type, $values->size);
    }

    private static ?self $emptyInstance = null;

    private static function empty(): self
    {
        if (self::$emptyInstance === null) {
            $type = 'image/png';
            $content = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');
            $size = strlen($content);
            self::$emptyInstance = new self($content, $type, $size);
        }
        return self::$emptyInstance;
    }
}
