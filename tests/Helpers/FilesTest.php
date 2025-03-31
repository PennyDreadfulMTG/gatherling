<?php

declare(strict_types=1);

namespace Gatherling\Tests\Helpers;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function Gatherling\Helpers\files;

class FilesTest extends TestCase
{
    public function testWithoutSetting(): void
    {
        self::assertNull(files()->optionalFile('userfile'));
        $this->expectException(InvalidArgumentException::class);
        files()->file('userfile');
    }

    public function testNullFiles(): void
    {
        $_FILES = [];
        self::assertNull(files()->optionalFile('userfile'));
        $this->expectException(InvalidArgumentException::class);
        files()->file('userfile');
    }

    public function testEmptyFiles(): void
    {
        $_FILES = ['userfile' => []];
        self::assertNull(files()->optionalFile('userfile'));
        $this->expectException(InvalidArgumentException::class);
        files()->file('userfile');
    }

    public function testIncompleteFiles(): void
    {
        $_FILES = ['userfile' => ['name' => 'test.txt']];
        self::assertNull(files()->optionalFile('userfile'));
        $this->expectException(InvalidArgumentException::class);
        files()->file('userfile');
    }

    public function testValidFiles(): void
    {
        $_FILES = ['userfile' => [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'size' => 100,
            'tmp_name' => 'jkfd93jkfjsofhjaifjasklfjdkla.txt',
            'error' => UPLOAD_ERR_OK,
            'full_path' => '/tmp/test.txt',
        ]];
        foreach ([files()->optionalFile('userfile'), files()->file('userfile')] as $file) {
            self::assertNotNull($file);
            self::assertSame('test.txt', $file->name);
            self::assertSame('text/plain', $file->type);
            self::assertSame(100, $file->size);
            self::assertSame('jkfd93jkfjsofhjaifjasklfjdkla.txt', $file->tmp_name);
            self::assertSame(UPLOAD_ERR_OK, $file->error);
            self::assertSame('/tmp/test.txt', $file->full_path);
        }
    }

    public function testUploadFailed(): void
    {
        $_FILES = ['userfile' => [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'size' => 100,
            'tmp_name' => 'jkfd93jkfjsofhjaifjasklfjdkla.txt',
            'error' => UPLOAD_ERR_NO_FILE,
            'full_path' => '/tmp/test.txt',
        ]];
        foreach ([files()->optionalFile('userfile'), files()->file('userfile')] as $file) {
            self::assertNotNull($file);
            self::assertSame('test.txt', $file->name);
            self::assertSame('text/plain', $file->type);
            self::assertSame(100, $file->size);
            self::assertSame('jkfd93jkfjsofhjaifjasklfjdkla.txt', $file->tmp_name);
            self::assertSame(UPLOAD_ERR_NO_FILE, $file->error);
            self::assertSame('/tmp/test.txt', $file->full_path);
        }
    }
}
