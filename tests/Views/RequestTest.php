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
        $request = new Request(['foo' => ['1', '2', '3']]);
        $this->assertEquals([1, 2, 3], $request->listInt('foo'));
        $this->assertEquals([], $request->listInt('bar'));
    }

    public function testDictString(): void
    {
        $request = new Request(['foo' => ['w' => 'w', 'b' => 'b', 'u' => 'u']]);
        $this->assertEquals(['w' => 'w', 'b' => 'b', 'u' => 'u'], $request->dictString('foo'));
        $this->assertEquals([], $request->dictString('bar'));
    }
}
