<?php

namespace sndsgd\yaml\callback\type;

class BooleanCallbackTest extends \PHPUnit\Framework\TestCase
{
    private $callback;
    private $context;

    public function setup()
    {
        $this->callback = new BooleanCallback();
        $this->context = new \sndsgd\yaml\ParserContext();
    }

    /**
     * @expectedException \sndsgd\yaml\ParserException
     * @expectedExceptionMessage expecting the tag without any content
     * @dataProvider provideExecuteInvalidValueException
     */
    public function testExecuteInvalidValueException($value)
    {
        $this->callback->execute("!type/boolean", $value, 0, $this->context);
    }

    public function provideExecuteInvalidValueException(): array
    {
        return [
            ["not empty"],
            ["1.2"],
            [123.456],
        ];
    }

    /**
     * @dataProvider provideExecute
     */
    public function testExecute(string $tag, $value, array $expect)
    {
        $this->assertSame($expect, $this->callback->execute($tag, $value, 0, $this->context));
    }

    public function provideExecute(): array
    {
        return [
            [
                "!type/boolean",
                "",
                ["type" => "boolean"],
            ],
            [
                "!type/boolean",
                ["default" => false],
                ["type" => "boolean", "default" => false],
            ],
        ];
    }
}
