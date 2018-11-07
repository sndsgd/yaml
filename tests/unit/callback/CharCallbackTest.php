<?php

namespace sndsgd\yaml\callback;

class CharCallbackTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @expectedException \UnexpectedValueException
     */
    public function testExecuteInvalidTagException()
    {
        $callback = new CharCallback("", "!not_valid");
    }

    /**
     * @expectedException \sndsgd\yaml\ParserException
     * @expectedExceptionMessage expecting the tag without any content
     * @dataProvider provideExecuteInvalidValueException
     *
     * @param mixed $value The value that will trigger the exception
     */
    public function testExecuteInvalidValueException($value)
    {
        $callback = new CharCallback($value, "!char");
        $callback->execute(new \sndsgd\yaml\ParserContext());
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
        $callback = new CharCallback($value, $tag);
        $callback->execute(new \sndsgd\yaml\ParserContext());
    }

    /**
     * Data provider for `testExecuteInvalidKey`
     *
     * @return array
     */
    public function provideExecuteInvalidKey(): array
    {
        return [
            [["type" => "abc"], "!char"],
            [["type" => "def"], "!varchar"],
        ];
    }

    /**
     * @dataProvider provideExecute
     */
    public function testExecute(string $tag, $value, array $expect)
    {
        $callback = new CharCallback($value, $tag);
        $this->assertSame($expect, $callback->execute(new \sndsgd\yaml\ParserContext()));
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
                "!char",
                "",
                ["type" => "string", "length" => "255", "isFixed" => true],
            ],
            [
                "!varchar",
                "",
                ["type" => "string", "length" => "255", "isFixed" => false],
            ],
            [
                "!char",
                100,
                ["type" => "string", "length" => "100", "isFixed" => true],
            ],
            [
                "!char",
                "100",
                ["type" => "string", "length" => "100", "isFixed" => true],
            ],
            [
                "!varchar",
                101,
                ["type" => "string", "length" => "101", "isFixed" => false],
            ],
            [
                "!char",
                ["length" => 42],
                ["type" => "string", "length" => "42", "isFixed" => true],
            ],
            [
                "!char",
                ["length" => 123, "isNullable" => true],
                ["type" => "string", "length" => "123", "isFixed" => true, "isNullable" => true],
            ],
        ];
    }
}
