<?php

namespace sndsgd\yaml\callback\rule;

class MinimumCallbackTest extends \PHPUnit\Framework\TestCase
{
    private $callback;
    private $context;

    public function setup()
    {
        $this->callback = new MinimumCallback();
        $this->context = new \sndsgd\yaml\ParserContext();
    }

    /**
     * @dataProvider provideExecuteException
     * @expectedException sndsgd\yaml\ParserException
     */
    public function testExecuteException($tag, $value)
    {
        $this->callback->execute($tag, $value, 0, $this->context);
    }

    public function provideExecuteException(): array
    {
        return [
            ["!rule/min", new \stdClass()],
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
                "!rule/min",
                1,
                ["rule" => \sndsgd\rule\MinRule::class, "min" => "1"],
            ],
            [
                "!rule/min",
                "1",
                ["rule" => \sndsgd\rule\MinRule::class, "min" => "1"],
            ],
            [
                "!rule/min",
                10,
                ["rule" => \sndsgd\rule\MinRule::class, "min" => "10"],
            ],
            [
                "!rule/min",
                ["min" => 42, "whatever" => "abc"],
                ["rule" => \sndsgd\rule\MinRule::class, "min" => "42", "whatever" => "abc"],
            ],
        ];
    }
}