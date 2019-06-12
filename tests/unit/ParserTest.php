<?php

namespace sndsgd\yaml;

class ParserTest extends \PHPUnit\Framework\TestCase
{
    use \phpmock\phpunit\PHPMock;

    public function testConstructorExtensionNotLoadedException()
    {
        $extensionLoadedMock = $this->getFunctionMock(__NAMESPACE__, "extension_loaded");
        $extensionLoadedMock->expects($this->any())->willReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("the YAML extension must be installed");
        new Parser();
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
        $this->expectException(\Exception::class);
        (new Parser())->parse("---\na:1\n---\nb:1", 1);
    }

    public function testParseFileReadException()
    {
        $this->expectException(\Exception::class);
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

    public function testHandleYamlParseError()
    {
        $this->expectException(\sndsgd\yaml\ParserException::class);
        $this->expectExceptionMessage("parsing error encountered during parsing");
        (new Parser())->parse("key: {\n");
    }

    /**
     * @dataProvider provideFixtures
     */
    public function testFixtures(string $path)
    {
        $callbacks = [
            new callback\SecondsCallback(),
            new callback\rule\LengthCallback(),
            new callback\rule\MinimumCallback(),
            new callback\rule\RequiredCallback(),
            new callback\type\CharCallback(),
            new callback\type\IntegerCallback(),
        ];

        $parser = new Parser(new ParserContext(), ...$callbacks);
        [$test, $expect] = $parser->parseFile($path, 2);

        $this->assertSame($expect, $test);
    }

    public function provideFixtures(): array
    {
        return [
            [__DIR__ . "/fixture_01.yaml"],
            [__DIR__ . "/fixtures/rules.yaml"],
        ];
    }

    public function testParseCreateDeferrableCallback()
    {
        $parser = new Parser(new ParserContext(), new callback\SecondsCallback());
        $result = $parser->parse("seconds: !seconds/defer 1 minute");
        $this->assertInstanceOf(DeferrableCallback::class, $result["seconds"]);
        $this->assertSame(60, $result["seconds"]->execute(new ParserContext()));
    }

    /**
     * @dataProvider provideParseFilesVariableFilter
     */
    public function testParseFilesVariableFilter(
        array $contents,
        bool $filterVariables,
        array $expect
    ): void
    {
        $files = [];
        foreach ($contents as $fileContent) {
            $file = \sndsgd\fs\Temp::createFile(uniqid());
            $file->write($fileContent);
            $files[] = $file->getPath();
        }

        $result = (new Parser())->parseFiles($files, 1, $filterVariables);
        $this->assertSame($expect, $result);
    }

    public function provideParseFilesVariableFilter(): array
    {
        return [
            [
                [
                    "---\n_: &foo this is the value",
                    "foo: *foo",
                ],
                false,
                [
                    "_" =>  "this is the value",
                    "foo" => "this is the value",
                ],
            ],
            [
                [
                    "---\n_: &foo this is the value",
                    "foo: *foo",
                ],
                true,
                [
                    "foo" => "this is the value",
                ],
            ],
            [
                [
                    "---\n_: &foo1 foo1\n_: &foo2 foo2",
                    "foo: whatever\nused_var: *foo1\nother_user_var: *foo2\n",
                ],
                true,
                [
                    "foo" => "whatever",
                    "used_var" => "foo1",
                    "other_user_var" => "foo2",
                ],
            ],
        ];
    }
}
