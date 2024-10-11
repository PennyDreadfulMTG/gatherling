<?php

declare(strict_types=1);

namespace Gatherling\Tests\Views;

use Gatherling\Views\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testInt(): void
    {
        $request = new Request(['foo' => '123', 'bar' => '123.4']);
        $this->assertEquals(123, $request->int('foo'));
        $this->expectException(\InvalidArgumentException::class);
        $request->int('bar');
        // BAKERT can you do two expectEXceptions? Do they interferer?
        $this->expectException(\InvalidArgumentException::class);
        $request->int('baz');
    }

    public function testOptionalInt(): void
    {
        $request = new Request(['foo' => '123', 'bar' => '123.4']);
        $this->assertEquals(123, $request->optionalInt('foo'));
        $this->assertNull($request->optionalInt('baz'));
        $this->expectException(\InvalidArgumentException::class);
        $request->optionalInt('bar');
    }

    public function testFloat(): void
    {
        $request = new Request(['foo' => '123.45']);
        $this->assertEquals(123.45, $request->float('foo'));
    }

    public function testString(): void
    {
        $request = new Request(['foo' => 'hello']);
        $this->assertEquals('hello', $request->string('foo'));
        $this->assertEquals('hello', $request->string('foo', 'other'));
        $this->assertEquals('hello', $request->string('bar', 'hello'));
        $this->expectException(\InvalidArgumentException::class);
        $request->string('bar');
    }

    public function testOptionalString(): void
    {
        $request = new Request(['foo' => 'hello']);
        $this->assertEquals('hello', $request->optionalString('foo'));
        $this->assertNull($request->optionalString('bar'));
    }

    public function testListInt(): void
    {
        $request = new Request(['foo' => ['1', '2', '3'], 'bar' => ['a', 'b', 'c']]);
        $this->assertEquals([1, 2, 3], $request->listInt('foo'));
        $this->assertEquals([], $request->listInt('baz'));
        $this->expectException(\InvalidArgumentException::class);
        $request->listInt('bar');
    }

    public function testListString(): void
    {
        $request = new Request(['foo' => ['1', '2', '3'], 'bar' => ['a', 'b', 'c']]);
        $this->assertEquals(['1', '2', '3'], $request->listString('foo'));
        $this->assertEquals(['a', 'b', 'c'], $request->listString('bar'));
        $this->assertEquals([], $request->listString('baz'));
    }

    public function testDictIntOrString(): void
    {
        $request = new Request(['foo' => ['a' => '1', 'b' => '2', 'c' => 'hello']]);
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 'hello'], $request->dictIntOrString('foo'));
        $this->assertEquals([], $request->dictIntOrString('bar'));
    }

    public function testDictString(): void
    {
        $request = new Request(['foo' => ['w' => 'w', 'b' => 'b', 'u' => 'u']]);
        $this->assertEquals(['w' => 'w', 'b' => 'b', 'u' => 'u'], $request->dictString('foo'));
        $this->assertEquals([], $request->dictString('bar'));
    }
}
