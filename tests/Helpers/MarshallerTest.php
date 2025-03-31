<?php

declare(strict_types=1);

namespace Gatherling\Tests\Helpers;

use Gatherling\Exceptions\MarshalException;
use PHPUnit\Framework\TestCase;
use stdClass;

use function Gatherling\Helpers\marshal;

class MarshallerTest extends TestCase
{
    public function testInt(): void
    {
        self::assertEquals(123, marshal(123)->int());
        self::assertEquals(456, marshal('456')->int());
        self::assertEquals(789, marshal(null)->int(789));
        $this->expectException(MarshalException::class);
        marshal('abc')->int();
        $this->expectException(MarshalException::class);
        marshal(null)->int();
        $this->expectException(MarshalException::class);
        marshal(123.45)->int();
    }

    public function testOptionalInt(): void
    {
        self::assertEquals(123, marshal(123)->optionalInt());
        self::assertEquals(456, marshal('456')->optionalInt());
        self::assertNull(marshal(null)->optionalInt());
        $this->expectException(MarshalException::class);
        marshal('abc')->optionalInt();
        $this->expectException(MarshalException::class);
        marshal(123.45)->optionalInt();
    }

    public function testOptionalIntThrowsOnEmptyString(): void
    {
        $this->expectException(MarshalException::class);
        marshal('')->optionalInt();
    }

    public function testString(): void
    {
        self::assertEquals('hello', marshal('hello')->string('key'));
        self::assertEquals('default', marshal(null)->string('default'));
        $this->expectException(MarshalException::class);
        marshal(123)->string();
        $this->expectException(MarshalException::class);
        marshal(null)->string();
    }

    public function testOptionalString(): void
    {
        self::assertEquals('hello', marshal('hello')->optionalString());
        self::assertNull(marshal(null)->optionalString());
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
        self::assertEquals([1, 2, 3], marshal([1, 2, 3])->ints());
        self::assertEquals([], marshal(null)->ints());
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
        self::assertEquals(['a', 'b', 'c'], marshal(['a', 'b', 'c'])->strings());
    }

    public function testDictInt(): void
    {
        self::assertEquals(['a' => 1, 'b' => 2], marshal(['a' => 1, 'b' => 2])->dictInt());
        $this->expectException(MarshalException::class);
        marshal('not an array')->dictInt();
        $this->expectException(MarshalException::class);
        marshal(['a' => 1, 'b' => 'string'])->dictInt();
    }

    public function testDictString(): void
    {
        self::assertEquals(['a' => 'hello', 'b' => 'world'], marshal(['a' => 'hello', 'b' => 'world'])->dictString());
        $this->expectException(MarshalException::class);
        marshal('not an array')->dictString();
        $this->expectException(MarshalException::class);
        marshal(['a' => 'hello', 'b' => 123])->dictString();
    }

    public function testStringThrowsOnObject(): void
    {
        $this->expectException(MarshalException::class);
        marshal(new stdClass())->string();
    }

    public function testDictIntOrString(): void
    {
        $input = ['a' => 1, 'b' => 'hello', 'c' => '99'];
        self::assertSame(['a' => 1, 'b' => 'hello', 'c' => 99], marshal($input)->dictIntOrString());
    }

    public function testDictIntOrStringThrowsOnFloat(): void
    {
        $this->expectException(MarshalException::class);
        marshal(['a' => 1, 'b' => 'hello', 'c' => 99.99])->dictIntOrString();
    }

    public function testDictIntOrStringThrowsOnNull(): void
    {
        $this->expectException(MarshalException::class);
        marshal(['a' => 1, 'b' => 'hello', 'c' => null])->dictIntOrString();
    }
}
