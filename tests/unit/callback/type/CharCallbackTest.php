<?php

namespace sndsgd\yaml\callback\type;

class CharCallbackTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @expectedException \sndsgd\yaml\ParserException
     * @expectedExceptionMessage expecting the tag without any content
     * @dataProvider provideExecuteInvalidValueException
     *
     * @param mixed $value The value that will trigger the exception
     */
    public function testExecuteInvalidValueException($value)
    {
        $callback = new CharCallback();
        $callback->execute("!type/char", $value, 0, new \sndsgd\yaml\ParserContext());
    }

    /**
     * Data provider for `testExecuteInvalidValueException`
     *
     * @return array
     */
    public function provideExecuteInvalidValueException(): array
    {
        return [
            ["not empty"],
            ["1.2"],
            [123.456],
        ];
    }

    /**
     * @expectedException \sndsgd\yaml\ParserException
     * @expectedExceptionMessage would be overwritten
     * @dataProvider provideExecuteInvalidKey
     */
    public function testExecuteInvalidKey(array $value, string $tag)
    {
        $callback = new CharCallback();
        $callback->execute($tag, $value, 0, new \sndsgd\yaml\ParserContext());
    }

    /**
     * Data provider for `testExecuteInvalidKey`
     *
     * @return array
     */
    public function provideExecuteInvalidKey(): array
    {
        return [
            [["type" => "abc"], "!type/char"],
            [["type" => "def"], "!type/varchar"],
        ];
    }

    /**
     * @dataProvider provideExecute
     */
    public function testExecute(string $tag, $value, array $expect)
    {
        $callback = new CharCallback();
        $this->assertSame($expect, $callback->execute($tag, $value, 0, new \sndsgd\yaml\ParserContext()));
    }

    /**
     * Data provider for `testExecute`
     *
     * @return array
     */
    public function provideExecute(): array
    {
        return [
            [
                "!type/char",
                "",
                ["type" => "string", "length" => "255", "isFixed" => true],
            ],
            [
                "!type/varchar",
                "",
                ["type" => "string", "length" => "255", "isFixed" => false],
            ],
            [
                "!type/char",
                100,
                ["type" => "string", "length" => "100", "isFixed" => true],
            ],
            [
                "!type/char",
                "100",
                ["type" => "string", "length" => "100", "isFixed" => true],
            ],
            [
                "!type/varchar",
                101,
                ["type" => "string", "length" => "101", "isFixed" => false],
            ],
            [
                "!type/char",
                ["length" => 42],
                ["type" => "string", "length" => "42", "isFixed" => true],
            ],
            [
                "!type/char",
                ["length" => 123, "isNullable" => true],
                ["type" => "string", "length" => "123", "isFixed" => true, "isNullable" => true],
            ],
        ];
    }
}
