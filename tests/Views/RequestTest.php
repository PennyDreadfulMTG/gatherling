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

    public function testListInt(): void
    {
        $request = new Request(['foo' => ['1', '2', '3']]);
        $this->assertEquals([1, 2, 3], $request->listInt('foo'));
        $this->assertEquals([], $request->listInt('bar'));
    }
}
