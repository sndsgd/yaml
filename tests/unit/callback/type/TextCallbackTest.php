<?php

namespace sndsgd\yaml\callback\type;

class TextCallbackTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @expectedException \UnexpectedValueException
     */
    public function testExecuteInvalidTagException()
    {
        $callback = new TextCallback();
        $callback->execute("!type/invalid", "", 0, new \sndsgd\yaml\ParserContext());
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
        $callback = new TextCallback();
        $callback->execute("!type/text", $value, 0, new \sndsgd\yaml\ParserContext());
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
        $callback = new TextCallback();
        $callback->execute("!type/text", $value, 0, new \sndsgd\yaml\ParserContext());
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
        $callback = new TextCallback();
        $this->assertSame(
            $expect,
            $callback->execute($tag, $value, 0, new \sndsgd\yaml\ParserContext())
        );
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
                "!type/tinytext",
                "",
                ["type" => "string", "length" => "255"]
            ],
            [
                "!type/text",
                "",
                ["type" => "string", "length" => "65535"]
            ],
            [
                "!type/mediumtext",
                "",
                ["type" => "string", "length" => "16777215"]
            ],
            [
                "!type/longtext",
                "",
                ["type" => "string", "length" => "4294967295"]
            ],
            [
                "!type/tinytext",
                ["isNullable" => true],
                ["type" => "string", "length" => "255", "isNullable" => true],
            ],
            [
                "!type/mediumtext",
                ["isNullable" => true],
                ["type" => "string", "length" => "16777215", "isNullable" => true],
            ],
        ];
    }
}
