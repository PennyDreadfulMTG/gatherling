<?php

declare(strict_types=1);

namespace Gatherling\Tests\Exceptions;

use Gatherling\Exceptions\RequestException;
use Gatherling\Helpers\Types\DictType;
use Gatherling\Helpers\Types\ListType;
use Gatherling\Helpers\Types\SimpleType;
use Gatherling\Helpers\Types\UnionType;
use PHPUnit\Framework\TestCase;

class RequestExceptionTest extends TestCase
{
    public function testUserMessage(): void
    {
        $e = new RequestException('foo', SimpleType::INT, 'bar', new \Exception());
        $this->assertEquals("The foo field requires a number but it was 'bar'", $e->getUserMessage());
        $e = new RequestException('foo', SimpleType::INT, null, new \Exception());
        $this->assertEquals("The foo field requires a number but it was missing", $e->getUserMessage());
        $e = new RequestException('foo', SimpleType::INT, '', new \Exception());
        $this->assertEquals("The foo field requires a number but it was blank", $e->getUserMessage());
        $e = new RequestException('foo', SimpleType::INT, '123.4', new \Exception());
        $this->assertEquals("The foo field requires a whole number but it was '123.4'", $e->getUserMessage());
        $e = new RequestException('foo', SimpleType::INT, [], new \Exception());
        $this->assertEquals("The foo field requires a number but it was invalid", $e->getUserMessage());
        $e = new RequestException('foo', SimpleType::STRING, null, new \Exception());
        $this->assertEquals("The foo field requires some text but it was missing", $e->getUserMessage());
        $e = new RequestException('foo', SimpleType::STRING, '', new \Exception());
        $this->assertEquals("The foo field requires some text but it was blank", $e->getUserMessage());
        $e = new RequestException('foo', SimpleType::STRING, '123', new \Exception());
        $this->assertEquals("The foo field requires some text but it was '123'", $e->getUserMessage());
        $e = new RequestException('foo', SimpleType::STRING, [], new \Exception());
        $this->assertEquals("The foo field requires some text but it was invalid", $e->getUserMessage());
        $e = new RequestException('foo', new UnionType(SimpleType::INT, SimpleType::STRING), '123', new \Exception());
        $this->assertEquals("The foo field requires a number or some text but it was '123'", $e->getUserMessage());
        $e = new RequestException('foo', new UnionType(SimpleType::INT, SimpleType::STRING), [], new \Exception());
        $this->assertEquals("The foo field requires a number or some text but it was invalid", $e->getUserMessage());
        $e = new RequestException('foo', new UnionType(SimpleType::INT, SimpleType::STRING), null, new \Exception());
        $this->assertEquals("The foo field requires a number or some text but it was missing", $e->getUserMessage());
        $e = new RequestException('foo', ListType::int(), null, new \Exception());
        $this->assertEquals("The foo field requires a list of numbers but it was missing", $e->getUserMessage());
        $e = new RequestException('foo', ListType::int(), '', new \Exception());
        $this->assertEquals("The foo field requires a list of numbers but it was blank", $e->getUserMessage());
        $e = new RequestException('foo', ListType::int(), 'not-an-array', new \Exception());
        $this->assertEquals("The foo field requires a list of numbers but it was 'not-an-array'", $e->getUserMessage());
        $e = new RequestException('foo', ListType::int(), ['a', 'b'], new \Exception());
        $this->assertEquals("The foo field requires a list of numbers but it was invalid", $e->getUserMessage());
        $e = new RequestException('foo', ListType::string(), [1, 2], new \Exception());
        $this->assertEquals("The foo field requires a list of text but it was invalid", $e->getUserMessage());
        $e = new RequestException('foo', DictType::int(), null, new \Exception());
        $this->assertEquals("The foo field requires a labeled list of numbers but it was missing", $e->getUserMessage());
        $e = new RequestException('foo', DictType::string(), ['a' => 1], new \Exception());
        $this->assertEquals("The foo field requires a labeled list of text but it was invalid", $e->getUserMessage());
        $e = new RequestException('foo', DictType::int(), ['a' => 'b'], new \Exception());
        $this->assertEquals("The foo field requires a labeled list of numbers but it was invalid", $e->getUserMessage());
        $e = new RequestException('foo', DictType::intOrString(), null, new \Exception());
        $this->assertEquals("The foo field requires a labeled list of numbers or text but it was missing", $e->getUserMessage());
        $e = new RequestException('foo', DictType::intOrString(), ['a' => []], new \Exception());
        $this->assertEquals("The foo field requires a labeled list of numbers or text but it was invalid", $e->getUserMessage());
    }
}
