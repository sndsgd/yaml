<?php

namespace sndsgd\yaml;

class ParserFilesTest extends \PHPUnit\Framework\TestCase
{
    const FIXTURE_DIR = __DIR__ . "/fixtures";
    const FILE_01 = self::FIXTURE_DIR . "/parser_files_01.yaml";
    const FILE_02 = self::FIXTURE_DIR . "/parser_files_02.yaml";
    const FILE_03 = self::FIXTURE_DIR . "/parser_files_03.yaml";
    const FILE_ERROR = self::FIXTURE_DIR . "/parser_files_error.yaml";

    public function testConstructorEmptyPathsException()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("at least one file path is required");
        new ParserFiles();
    }

    public function testGetErrorMessageSingle(): void
    {
        $this->expectException(\sndsgd\yaml\ParserException::class);
        $this->expectExceptionMessage("(line 8, column 1)");
        (new Parser())->parseFiles([self::FILE_ERROR]);
    }

    public function testGetErrorMessage02(): void
    {
        $this->expectException(\sndsgd\yaml\ParserException::class);
        $this->expectExceptionMessage("parser_files_error.yaml, line 8, column 1)");
        (new Parser())->parseFiles([self::FILE_ERROR, self::FILE_01]);
    }

    public function testGetErrorMessage03(): void
    {
        $this->expectException(\sndsgd\yaml\ParserException::class);
        $this->expectExceptionMessage("parser_files_error.yaml, line 8, column 1)");
        (new Parser())->parseFiles([self::FILE_01, self::FILE_ERROR]);
    }

    public function testGetErrorMessage04(): void
    {
        $this->expectException(\sndsgd\yaml\ParserException::class);
        $this->expectExceptionMessage("parser_files_error.yaml, line 8, column 1)");
        (new Parser())->parseFiles([self::FILE_01, self::FILE_02, self::FILE_ERROR]);
    }
}
