<?php

namespace sndsgd\yaml;

class ParserTest extends \PHPUnit\Framework\TestCase
{
    public function testWithContext()
    {
        $p1 = new Parser();
        $p2 = $p1->withContext(new ParserContext());
        $this->assertTrue($p1 !== $p2);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testParseMaxDocumentsException()
    {
        $parser = new Parser();
        $parser->parse("", -1);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testParsePrependedLinesException()
    {
        $parser = new Parser();
        $parser->parse("", 0, -1);
    }

    /**
     * @expectedException \Exception
     */
    public function testParseMaxDocumentsExceededException()
    {
        $parser = new Parser();
        $parser->parse("---\na:1\n---\nb:1", 1);
    }

    /**
     * @dataProvider provideExecuteTagCallback
     */
    public function testExecuteTagCallback(
        array $parserCallbacks,
        $value,
        string $tag,
        int $flags,
        $expect
    )
    {
        $parser = new Parser(new ParserContext(), ...$parserCallbacks);
        $result = $parser->executeTagCallback($value, $tag, $flags);
        $this->assertSame($expect, $result);
    }

    /**
     * Data provider for `testExecuteTagCallback`
     *
     * @return array
     */
    public function provideExecuteTagCallback(): array
    {
        return [
            [
                [new callback\SecondsCallback()],
                "1 hour",
                "!seconds",
                0,
                3600,
            ],
            [
                [],
                "",
                "!not_found",
                0,
                "!not_found",
            ],
        ];
    }

    /**
     * @expectedException sndsgd\yaml\ParserException
     */
    public function provideExecuteTagCallbackException()
    {
        $parser = new Parser();
        $parser->executeTagCallback("not empty", "!anything");
    }

    /**
     * @expectedException sndsgd\yaml\ParserException
     */
    public function testHandleYamlParseError()
    {
        $parser = new Parser();
        $parser->parse("key: {\n");
    }

    /**
     * @dataProvider provideFixtures
     *
     * @param string $path
     * @return [type] [description]
     */
    public function testFixtures(array $callbacks, string $path)
    {
        $parser = new Parser(new ParserContext(), ...$callbacks);
        [$test, $expect] = $parser->parseFile($path, 2);

        $this->assertSame($expect, $test);
    }

    /**
     * Data provider for `testFixtures`
     *
     * @return array
     */
    public function provideFixtures(): array
    {
        $callbacks = [
            new callback\SecondsCallback(),
            new callback\CharCallback(),
            new callback\CharCallback(),
            new callback\UnixTimestampCallback(),
        ];

        return [
            [$callbacks, __DIR__."/fixture_01.yaml"],
        ];
    }
}
