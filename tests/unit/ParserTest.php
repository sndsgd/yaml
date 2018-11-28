<?php

namespace sndsgd\yaml;

class ParserTest extends \PHPUnit\Framework\TestCase
{
    use \phpmock\phpunit\PHPMock;

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage the YAML extension must be installed
     */
    public function testConstructorExtensionNotLoadedException()
    {
        $extensionLoadedMock = $this->getFunctionMock(__NAMESPACE__, "extension_loaded");
        $extensionLoadedMock->expects($this->any())->willReturn(false);

        new Parser();
    }

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
     * @expectedException Exception
     * @expectedExceptionMessage failed to parse YAML file; failed to read file
     */
    public function testParseFileReadException()
    {
        $parser = new Parser();
        $parser->parseFile(__DIR__);
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
     * @expectedException \sndsgd\yaml\ParserException
     * @expectedExceptionMessage did not find expected node content (line 2, column 1)
     */
    public function testHandlerParseErrorWithPrependedLines()
    {
        $one = "---\none: 1\ntwo: 2\n";
        $two = "three: 3\nbad: {\n";

        $parser = new Parser();
        $parser->parse($one . $two, 1, 4);
    }

    /**
     * @expectedException sndsgd\yaml\ParserException
     * @expectedExceptionMessage parsing error encountered during parsing
     */
    public function testHandleYamlParseError()
    {
        $parser = new Parser();
        $parser->parse("key: {\n");
    }

    /**
     * @dataProvider provideFixtures
     */
    public function testFixtures(array $callbacks, string $path)
    {
        $parser = new Parser(new ParserContext(), ...$callbacks);
        [$test, $expect] = $parser->parseFile($path, 2);

        $this->assertSame($expect, $test);
    }

    public function provideFixtures(): array
    {
        $callbacks = [
            new callback\SecondsCallback(),
            new callback\rule\LengthCallback(),
            new callback\rule\MinimumCallback(),
            new callback\rule\RequiredCallback(),
            new callback\type\CharCallback(),
            new callback\type\IntegerCallback(),
        ];

        return [
            [$callbacks, __DIR__."/fixture_01.yaml"],
            [$callbacks, __DIR__ . "/fixtures/rules.yaml"],
        ];
    }

    public function testParseCreateDeferrableCallback()
    {
        $parser = new Parser(new ParserContext(), new callback\SecondsCallback());
        $result = $parser->parse("seconds: !seconds/defer 1 minute");
        $this->assertInstanceOf(DeferrableCallback::class, $result['seconds']);
        $this->assertSame(60, $result['seconds']->execute(new ParserContext()));
    }
}
