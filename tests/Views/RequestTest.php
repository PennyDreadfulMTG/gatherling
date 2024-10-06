<?php

namespace Gatherling\Tests\Views;

use Gatherling\Views\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testInt(): void
    {
        $request = new Request(['foo' => '123'], ['foo' => '123'], ['foo' => 'hello']);
        $this->assertEquals(123, $request->int('foo'));
        $this->assertEquals(123, $request->get()->int('foo'));
        $this->expectException(\InvalidArgumentException::class);
        $request->post()->int('foo');
    }

    public function testOptionalInt(): void
    {
        $request = new Request(['foo' => '123'], [], ['foo' => '123']);
        $this->assertEquals(123, $request->optionalInt('foo'));
        $this->assertNull($request->get()->optionalInt('foo'));
        $this->assertEquals(123, $request->post()->optionalInt('foo'));
    }

    public function testListInt(): void
    {
        $request = new Request(['list' => ['1', '2', '3']], ['list' => ['1', '2', '3']], []);
        $this->assertEquals([1, 2, 3], $request->listInt('list'));
        $this->assertEquals([1, 2, 3], $request->get()->listInt('list'));
        $this->assertEquals([], $request->post()->listInt('list'));
    }
}
