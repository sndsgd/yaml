<?php

namespace sndsgd\yaml\callback;

class IntegerCallbackTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @expectedException \UnexpectedValueException
     */
    public function testExecuteInvalidTagException()
    {
        $callback = new IntegerCallback("", "!invalid");
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
        $callback = new IntegerCallback($value, "!uint32");
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
        $callback = new IntegerCallback($value, "!uint64");
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
            [["min" => 42]],
            [["max" => 42]],
        ];
    }

    /**
     * @dataProvider provideExecute
     */
    public function testExecute(string $tag, $value, array $expect)
    {
        $callback = new IntegerCallback($value, $tag);
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
                "!uint8",
                "",
                ["type" => "integer", "min" => "0", "max" => "255"],
            ],
            [
                "!int8",
                "",
                ["type" => "integer", "min" => "-128", "max" => "127"],
            ],
            [
                "!uint16",
                "",
                ["type" => "integer", "min" => "0", "max" => "65535"],
            ],
            [
                "!int16",
                "",
                ["type" => "integer", "min" => "32768", "max" => "32767"],
            ],
            [
                "!uint32",
                "",
                ["type" => "integer", "min" => "0", "max" => "4294967295"],
            ],
            [
                "!int32",
                "",
                ["type" => "integer", "min" => "-2147483648", "max" => "2147483647"],
            ],
            [
                "!uint64",
                "",
                ["type" => "integer", "min" => "0", "max" => "18446744073709551615"],
            ],
            [
                "!int64",
                "",
                ["type" => "integer", "min" => "-9223372036854775808", "max" => "9223372036854775807"],
            ],
            [
                "!uint32",
                ["isNullable" => true],
                [
                    "type" => "integer",
                    "min" => "0",
                    "max" => "4294967295",
                    "isNullable" => true
                ],
            ],
            [
                "!uint64",
                ["isNullable" => true, "whatever" => 1234],
                [
                    "type" => "integer",
                    "min" => "0",
                    "max" => "18446744073709551615",
                    "isNullable" => true,
                    "whatever" => 1234,
                ],
            ],
        ];
    }
}
