<?php

namespace sndsgd\yaml\callback;

class SecondsCallbackTest extends \PHPUnit\Framework\TestCase
{
    private $callback;
    private $context;

    public function setup()
    {
        $this->callback = new SecondsCallback();
        $this->context = new \sndsgd\yaml\ParserContext();
    }

    /**
     * @dataProvider provideExecuteBadValue
     * @expectedException \sndsgd\yaml\ParserException
     * @expectedExceptionMessage failed to convert
     */
    public function testExecuteBadValue($value)
    {
        $this->callback->execute("!seconds", $value, 0, $this->context);
    }

    /**
     * Data provider for `testExecuteBadValue`
     *
     * @return array
     */
    public function provideExecuteBadValue(): array
    {
        return [
            [""],
            [[]],
            [42],
            [12.34]
        ];
    }

    /**
     * @expectedException \sndsgd\yaml\ParserException
     * @expectedExceptionMessage failed to convert 'blegh' to seconds
     */
    public function testExecuteConvertFailure()
    {
        $this->callback->execute("!seconds", "blegh", 0, $this->context);
    }

    /**
     * @dataProvider provideExecute
     */
    public function testExecute($value, int $expect)
    {
        $this->assertSame(
            $expect,
            $this->callback->execute("!seconds", $value, 0, $this->context)
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
            ["1 day", 86400],
            ["2 days", 86400 * 2],
            ["1 hour", 3600 * 1],
            ["10 hours", 3600 * 10],
        ];
    }
}
