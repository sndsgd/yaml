<?php

namespace sndsgd\yaml\callback\rule;

class RequiredCallbackTest extends \PHPUnit\Framework\TestCase
{
    private $callback;
    private $context;

    public function setup()
    {
        $this->callback = new RequiredCallback();
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
            ["!rule/required", -1],
            ["!rule/required", "123"],
            ["!rule/required", new \stdClass()],
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
                "!rule/required",
                "",
                ["rule" => \sndsgd\rule\RequiredRule::class],
            ],
            [
                "!rule/required",
                ["error_message" => "this is required"],
                ["rule" => \sndsgd\rule\RequiredRule::class, "error_message" => "this is required"],
            ],
            [
                "!rule/required",
                ["foo" => 123, "bar" => 456],
                ["rule" => \sndsgd\rule\RequiredRule::class, "foo" => 123, "bar" => 456],
            ],
        ];
    }
}