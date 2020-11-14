<?php declare(strict_types=1);

namespace sndsgd\yaml\callbacks;

use sndsgd\yaml\exceptions\ParserException;
use sndsgd\yaml\ParserContext;

class SecondsCallbackTestish extends \PHPUnit\Framework\TestCase
{
    private $context;

    public function setup(): void
    {
        $this->context = new ParserContext();
    }

    /**
     * @dataProvider provideExecuteBadValue
     */
    public function testExecuteBadValue($value)
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("failed to convert");
        SecondsCallback::executeYamlCallback("!seconds", $value, 0, $this->context);
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
            [12.34],
        ];
    }

    public function testExecuteConvertFailure()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("failed to convert 'blegh' to seconds");
        SecondsCallback::executeYamlCallback("!seconds", "blegh", 0, $this->context);
    }

    /**
     * @dataProvider provideExecute
     */
    public function testExecute($value, int $expect)
    {
        $this->assertSame(
            $expect,
            SecondsCallback::executeYamlCallback("!seconds", $value, 0, $this->context),
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
