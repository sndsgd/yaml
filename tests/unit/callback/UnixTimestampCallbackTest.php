<?php

namespace sndsgd\yaml\callback;

class UnixTimestampCallbackTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @expectedException \UnexpectedValueException
     */
    public function testExecuteInvalidTagException()
    {
        $callback = new UnixTimestampCallback("", "!invalid");
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
        $callback = new UnixTimestampCallback($value, "!unix_timestamp");
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
        $callback = new UnixTimestampCallback($value, "!unix_timestamp");
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
        $callback = new UnixTimestampCallback($value, $tag);
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
                "!unix_timestamp",
                "",
                [
                    "type" => "integer",
                    "min" => "0",
                    "max" => "4294967295",
                    "comment" => "unix timestamp"
                ],
            ],
            [
                "!unix_timestamp",
                ["default" => 0],
                [
                    "type" => "integer",
                    "min" => "0",
                    "max" => "4294967295",
                    "default" => 0,
                    "comment" => "unix timestamp",
                ],
            ],
        ];
    }
}
