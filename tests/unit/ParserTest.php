<?php declare(strict_types=1);

namespace sndsgd\yaml;

use ErrorException;
use PHPUnit\Framework\TestCase;
use sndsgd\yaml\callbacks\SecondsCallback;
use sndsgd\yaml\exceptions\DuplicateCallbackTagException;
use sndsgd\yaml\exceptions\InvalidCallbackClassException;
use sndsgd\yaml\exceptions\ParserException;
use Throwable;
use UnexpectedValueException;

class ParserTest extends TestCase
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
        $this->expectException(UnexpectedValueException::class);
        (new Parser())->parse("", -1);
    }

    public function testParseMaxDocumentsExceededException()
    {
        $this->expectException(Throwable::class);
        (new Parser())->parse("---\na:1\n---\nb:1", 1);
    }

    /**
     * @dataProvider provideExecuteTagCallback
     */
    public function testExecuteTagCallback(
        array $parserCallbacks,
        $value,
        string $tag,
        int $flags,
        $expect,
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

    /**
     * @dataProvider provideParseMaxDocuments
     */
    public function testParseMaxDocuments(
        string $yaml,
        int $maxDocuments,
        array $expect,
    ): void {
        $parser = new Parser();
        $this->assertSame($expect, $parser->parse($yaml, $maxDocuments));
    }

    public function provideParseMaxDocuments(): array
    {
        return [
            [
                <<<YAML
                ---
                one: 1
                YAML,
                1,
                ["one" => 1],
            ],
            [
                <<<YAML
                ---
                one: 1
                YAML,
                0,
                [["one" => 1]],
            ],
            [
                <<<YAML
                ---
                one: 1
                ---
                two: 2
                YAML,
                2,
                [["one" => 1], ["two" => 2]],
            ],
            [
                <<<YAML
                ---
                one: 1
                ---
                two: 2
                YAML,
                0,
                [["one" => 1], ["two" => 2]],
            ],
            [
                <<<YAML
                ---
                one: 1
                ---
                two: 2
                ---
                three: 3
                YAML,
                3,
                [["one" => 1], ["two" => 2], ["three" => 3]],
            ],
        ];
    }

    /**
     * @dataProvider provideParseFiles
     */
    public function testParseFiles(
        string $path,
        int $maxDocuments,
        array $expect,
    ): void {
        $parser = new Parser();
        $this->assertSame($expect, $parser->parseFile($path, $maxDocuments));
    }

    public function provideParseFiles(): array
    {
        return [
            [
                __DIR__ . "/../fixtures/1-doc.yaml",
                1,
                ["one" => 1],
            ],
            [
                __DIR__ . "/../fixtures/1-doc.yaml",
                0,
                [["one" => 1]],
            ],
            [
                __DIR__ . "/../fixtures/2-doc.yaml",
                2,
                [["one" => 1], ["two" => 2]],
            ],
            [
                __DIR__ . "/../fixtures/2-doc.yaml",
                0,
                [["one" => 1], ["two" => 2]],
            ],
        ];
    }

    public function testParseFileException(): void
    {
        $parser = new Parser();
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage("parsing YAML file failed");
        $parser->parseFile(TESTS_DIR . "/fixtures/does-not-exist.yaml");
    }
}
