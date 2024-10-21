<?php

declare(strict_types=1);

namespace Gatherling\Helpers;

use InvalidArgumentException;

class Files
{
    /** @param array<string, mixed> $files */
    public function __construct(private array $files)
    {
    }

    public function file(string $key): File
    {
        if (!isset($this->files[$key])) {
            throw new InvalidArgumentException("File $key not found");
        }
        $file = $this->files[$key];
        if (!is_array($file)) {
            throw new InvalidArgumentException("File $key is not an array");
        }
        if (
            !isset($file['name'])
            || !is_string($file['name'])
            || !isset($file['type'])
            || !is_string($file['type'])
            || !isset($file['size'])
            || !is_int($file['size'])
            || !isset($file['tmp_name'])
            || !is_string($file['tmp_name'])
            || !isset($file['error'])
            || !is_int($file['error'])
            || !isset($file['full_path'])
            || !is_string($file['full_path'])
        ) {
            throw new InvalidArgumentException("$key is not a valid _FILES entry");
        }
        return new File(
            $file['name'],
            $file['type'],
            $file['size'],
            $file['tmp_name'],
            $file['error'],
            $file['full_path'],
        );
    }

    public function optionalFile(string $key): ?File
    {
        try {
            return $this->file($key);
        } catch (InvalidArgumentException) {
            return null;
        }
    }
}
