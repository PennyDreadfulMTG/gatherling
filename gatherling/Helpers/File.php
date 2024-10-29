<?php

declare(strict_types=1);

namespace Gatherling\Helpers;

class File
{
    public function __construct(
        public string $name,
        public string $type,
        public int $size,
        public string $tmp_name,
        public int $error,
        public string $full_path,
    ) {
    }
}
