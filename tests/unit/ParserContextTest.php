<?php declare(strict_types=1);

namespace sndsgd\yaml;

use LogicException;
use PHPUnit\Framework\TestCase;

class ParserContextTest extends TestCase
{
    /**
     * @dataProvider provideGetSet
     */
    public function testGetSet(
        string $key,
        mixed $value,
    ): void {
        $pc = new ParserContext();
        $pc->set($key, $value);
        $this->assertSame($value, $pc->get($key));
    }

    public function provideGetSet(): iterable
    {
        yield ["please", "work"];
        yield ["ugh", 123];
        yield ["hello", [1,2,3]];
    }

    public function testGetException(): void
    {
        $pc = new ParserContext();
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            "failed to retrieve 'nope' from sndsgd\yaml\ParserContext",
        );
        $pc->get("nope");

    }
}
