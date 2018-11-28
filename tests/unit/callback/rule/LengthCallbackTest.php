<?php

namespace sndsgd\yaml\callback\rule;

class LengthCallbackTest extends \PHPUnit\Framework\TestCase
{
    private $callback;
    private $context;

    public function setup()
    {
        $this->callback = new LengthCallback();
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
            ["!rule/length", 4.2],
            ["!rule/length", -1],
            ["!rule/length", "123"],
            ["!rule/length", new \stdClass()],
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
                "!rule/length",
                10,
                ["rule" => \sndsgd\rule\MinRule::class, "length" => 10],
            ],
            [
                "!rule/length",
                1,
                ["rule" => \sndsgd\rule\MinRule::class, "length" => 1],
            ],
            [
                "!rule/length",
                1,
                ["rule" => \sndsgd\rule\MinRule::class, "length" => 1],
            ],
            [
                "!rule/length",
                ["length" => 42, "whatever" => "abc"],
                ["rule" => \sndsgd\rule\MinRule::class, "length" => 42, "whatever" => "abc"],
            ],
        ];
    }
}