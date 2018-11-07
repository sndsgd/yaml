<?php

namespace sndsgd\yaml\callback;

class TextCallbackTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @expectedException \UnexpectedValueException
     */
    public function testExecuteInvalidTagException()
    {
        $callback = new TextCallback("", "!invalid");
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
        $callback = new TextCallback($value, "!text");
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
            [42],
            [123.456],
        ];
    }

    /**
     * @expectedException \sndsgd\yaml\ParserException
     * @expectedExceptionMessage would be overwritten
     * @dataProvider provideExecuteInvalidKey
     */
    public function testExecuteInvalidKey($value)
    {
        $callback = new TextCallback($value, "!text");
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
            [["type" => "abc"]],
            [["length" => 42]],
        ];
    }

    /**
     * @dataProvider provideExecute
     */
    public function testExecute(string $tag, $value, array $expect)
    {
        $callback = new TextCallback($value, $tag);
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
                "!tinytext",
                "",
                ["type" => "string", "length" => "255"]
            ],
            [
                "!text",
                "",
                ["type" => "string", "length" => "65535"]
            ],
            [
                "!mediumtext",
                "",
                ["type" => "string", "length" => "16777215"]
            ],
            [
                "!longtext",
                "",
                ["type" => "string", "length" => "4294967295"]
            ],
            [
                "!tinytext",
                ["isNullable" => true],
                ["type" => "string", "length" => "255", "isNullable" => true],
            ],
            [
                "!mediumtext",
                ["isNullable" => true],
                ["type" => "string", "length" => "16777215", "isNullable" => true],
            ],
        ];
    }
}
