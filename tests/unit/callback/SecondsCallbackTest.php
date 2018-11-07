<?php

namespace sndsgd\yaml\callback;

class SecondsCallbackTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @expectedException \sndsgd\yaml\ParserException
     * @expectedExceptionMessage expecting the tag followed by a human readable amount of time
     * @dataProvider provideExecuteBadValue
     */
    public function testExecuteBadValue($value)
    {
        $callback = new SecondsCallback($value, "!seconds");
        $callback->execute(new \sndsgd\yaml\ParserContext());
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
        $callback = new SecondsCallback("blegh", "!seconds");
        $callback->execute(new \sndsgd\yaml\ParserContext());
    }

    /**
     * @dataProvider provideExecute
     */
    public function testExecute($value, int $expect)
    {
        $callback = new SecondsCallback($value, "!seconds");
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
            ["1 day", 86400],
            ["2 days", 86400 * 2],
            ["1 hour", 3600 * 1],
            ["10 hours", 3600 * 10],
        ];
    }
}
