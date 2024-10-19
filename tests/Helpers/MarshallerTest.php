<?php

declare(strict_types=1);

namespace Gatherling\Tests\Helpers;

use Gatherling\Exceptions\MarshalException;
use PHPUnit\Framework\TestCase;

use function Gatherling\Helpers\marshal;

class MarshallerTest extends TestCase
{
    public function testInt(): void
    {
        $this->assertEquals(123, marshal(123)->int());
        $this->assertEquals(456, marshal('456')->int());
        $this->assertEquals(789, marshal(null)->int(789));
        $this->expectException(MarshalException::class);
        marshal('abc')->int();
        $this->expectException(MarshalException::class);
        marshal(null)->int();
        $this->expectException(MarshalException::class);
        marshal(123.45)->int();
    }

    public function testOptionalInt(): void
    {
        $this->assertEquals(123, marshal(123)->optionalInt());
        $this->assertEquals(456, marshal('456')->optionalInt());
        $this->assertNull(marshal(null)->optionalInt());
        $this->expectException(MarshalException::class);
        marshal('abc')->optionalInt();
        $this->expectException(MarshalException::class);
        marshal(123.45)->optionalInt();
    }

    public function testString(): void
    {
        $this->assertEquals('hello', marshal('hello')->string('key'));
        $this->assertEquals('default', marshal(null)->string('default'));
        $this->expectException(MarshalException::class);
        marshal(123)->string();
        $this->expectException(MarshalException::class);
        marshal(null)->string();
    }

    public function testOptionalString(): void
    {
        $this->assertEquals('hello', marshal('hello')->optionalString());
        $this->assertNull(marshal(null)->optionalString());
    }

    public function testOptionalStringThrowsOnInt(): void
    {
        $this->expectException(MarshalException::class);
        marshal(123)->optionalString();
    }

    public function testOptionalStringThrowsOnArray(): void
    {
        $this->expectException(MarshalException::class);
        marshal([])->optionalString();
    }

    public function testInts(): void
    {
        $this->assertEquals([1, 2, 3], marshal([1, 2, 3])->ints());
        $this->assertEquals([], marshal(null)->ints());
    }

    public function testIntsThrowsOnString(): void
    {
        $this->expectException(MarshalException::class);
        marshal('not an array')->ints();
    }

    public function testIntsThrowsOnStringInList(): void
    {
        $this->expectException(MarshalException::class);
        marshal(['a', 'b', 123])->ints();
    }

    public function testIntsThrowsOnNullInList(): void
    {
        $this->expectException(MarshalException::class);
        marshal([1, null, 3])->ints();
    }

    public function testIntsThrowsOnFloatInList(): void
    {
        $this->expectException(MarshalException::class);
        marshal([1, 123.45, 3])->ints();
    }

    public function testStrings(): void
    {
        $this->assertEquals(['a', 'b', 'c'], marshal(['a', 'b', 'c'])->strings());
    }

    public function testDictInt(): void
    {
        $this->assertEquals(['a' => 1, 'b' => 2], marshal(['a' => 1, 'b' => 2])->dictInt());
        $this->expectException(MarshalException::class);
        marshal('not an array')->dictInt();
        $this->expectException(MarshalException::class);
        marshal(['a' => 1, 'b' => 'string'])->dictInt();
    }

    public function testDictString(): void
    {
        $this->assertEquals(['a' => 'hello', 'b' => 'world'], marshal(['a' => 'hello', 'b' => 'world'])->dictString());
        $this->expectException(MarshalException::class);
        marshal('not an array')->dictString();
        $this->expectException(MarshalException::class);
        marshal(['a' => 'hello', 'b' => 123])->dictString();
    }
}
