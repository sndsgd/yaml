<?php declare(strict_types=1);

namespace sndsgd\yaml;

use sndsgd\yaml\callbacks\SecondsCallback;
use sndsgd\yaml\exceptions\DuplicateCallbackTagException;
use sndsgd\yaml\exceptions\InvalidCallbackClassException;
use sndsgd\yaml\exceptions\ParserException;

class ParserTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructorCallbackClassImplementsException()
    {
        $this->expectException(InvalidCallbackClassException::class);
        new Parser(null, ParserTest::class);
    }

    public function testConstructorDuplicateCallbackTagException()
    {
        $this->expectException(DuplicateCallbackTagException::class);
        new Parser(null, SecondsCallback::class, SecondsCallback::class);
    }

    public function testWithContext()
    {
        $p1 = new Parser();
        $p2 = $p1->withContext(new ParserContext());
        $this->assertTrue($p1 !== $p2);
    }

    public function testParseMaxDocumentsException()
    {
        $this->expectException(\UnexpectedValueException::class);
        (new Parser())->parse("", -1);
    }

    public function testParseMaxDocumentsExceededException()
    {
        $this->expectException(\Throwable::class);
        (new Parser())->parse("---\na:1\n---\nb:1", 1);
    }

    public function testParseFileReadException()
    {
        $this->expectException(\Throwable::class);
        $this->expectExceptionMessage("failed to parse YAML file; failed to read file");
        (new Parser())->parseFile(__DIR__);
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
                [SecondsCallback::class],
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

    public function testHandleYamlParseError()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("parsing error encountered during parsing");
        (new Parser())->parse("key: {\n");
    }
}
