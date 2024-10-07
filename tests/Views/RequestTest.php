<?php

namespace Gatherling\Tests\Views;

use Gatherling\Views\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testInt(): void
    {
        $request = new Request(['foo' => '123']);
        $this->assertEquals(123, $request->int('foo'));
        $this->expectException(\InvalidArgumentException::class);
        $request->int('bar');
    }

    public function testOptionalInt(): void
    {
        $request = new Request(['foo' => '123']);
        $this->assertEquals(123, $request->optionalInt('foo'));
        $this->assertNull($request->optionalInt('bar'));
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


    public function testDictString(): void
    {
        $request = new Request(['foo' => ['w' => 'w', 'b' => 'b', 'u' => 'u']]);
        $this->assertEquals(['w' => 'w', 'b' => 'b', 'u' => 'u'], $request->dictString('foo'));
        $this->assertEquals([], $request->dictString('bar'));
    }
}
