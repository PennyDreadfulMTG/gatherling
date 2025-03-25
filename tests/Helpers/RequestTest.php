<?php

declare(strict_types=1);

namespace Gatherling\Tests\Helpers;

use Gatherling\Exceptions\RequestException;
use Gatherling\Helpers\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testInt(): void
    {
        $request = new Request(['foo' => '123', 'bar' => '123.4']);
        self::assertEquals(123, $request->int('foo'));
        $this->expectException(RequestException::class);
        $request->int('bar');
    }

    public function testIntMissing(): void
    {
        $request = new Request(['foo' => '123', 'bar' => '123.4']);
        $this->expectException(RequestException::class);
        $request->int('baz');
    }

    public function testIntThrowsOnEmptyString(): void
    {
        $request = new Request(['foo' => '']);
        $this->expectException(RequestException::class);
        $request->int('foo');
    }

    public function testOptionalInt(): void
    {
        $request = new Request(['foo' => '123', 'bar' => '123.4']);
        self::assertEquals(123, $request->optionalInt('foo'));
        self::assertNull($request->optionalInt('baz'));
        $this->expectException(RequestException::class);
        $request->optionalInt('bar');
    }

    public function testOptionalIntReturnsNullOnEmptyString(): void
    {
        $request = new Request(['foo' => '']);
        self::assertNull($request->optionalInt('foo'));
    }

    public function testFloat(): void
    {
        $request = new Request(['foo' => '123.45']);
        self::assertEquals(123.45, $request->float('foo'));
    }

    public function testString(): void
    {
        $request = new Request(['foo' => 'hello']);
        self::assertEquals('hello', $request->string('foo'));
        self::assertEquals('hello', $request->string('foo', 'other'));
        self::assertEquals('hello', $request->string('bar', 'hello'));
        $this->expectException(RequestException::class);
        $request->string('bar');
    }

    public function testOptionalString(): void
    {
        $request = new Request(['foo' => 'hello']);
        self::assertEquals('hello', $request->optionalString('foo'));
        self::assertNull($request->optionalString('bar'));
    }

    public function testListInt(): void
    {
        $request = new Request(['foo' => ['1', '2', '3'], 'bar' => ['a', 'b', 'c']]);
        self::assertEquals([1, 2, 3], $request->listInt('foo'));
        self::assertEquals([], $request->listInt('baz'));
        $this->expectException(RequestException::class);
        $request->listInt('bar');
    }

    public function testListString(): void
    {
        $request = new Request(['foo' => ['1', '2', '3'], 'bar' => ['a', 'b', 'c']]);
        self::assertEquals(['1', '2', '3'], $request->listString('foo'));
        self::assertEquals(['a', 'b', 'c'], $request->listString('bar'));
        self::assertEquals([], $request->listString('baz'));
    }

    public function testDictIntOrString(): void
    {
        $request = new Request(['foo' => ['a' => '1', 'b' => '2', 'c' => 'hello']]);
        self::assertEquals(['a' => 1, 'b' => 2, 'c' => 'hello'], $request->dictIntOrString('foo'));
        self::assertEquals([], $request->dictIntOrString('bar'));
    }

    public function testDictString(): void
    {
        $request = new Request(['foo' => ['w' => 'w', 'b' => 'b', 'u' => 'u']]);
        self::assertEquals(['w' => 'w', 'b' => 'b', 'u' => 'u'], $request->dictString('foo'));
        self::assertEquals([], $request->dictString('bar'));
    }
}
