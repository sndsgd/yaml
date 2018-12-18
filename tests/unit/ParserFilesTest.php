<?php

namespace sndsgd\yaml;

class ParserFilesTest extends \PHPUnit\Framework\TestCase
{
    const FIXTURE_DIR = __DIR__ . "/fixtures";
    const FILE_01 = self::FIXTURE_DIR . "/parser_files_01.yaml";
    const FILE_02 = self::FIXTURE_DIR . "/parser_files_02.yaml";
    const FILE_03 = self::FIXTURE_DIR . "/parser_files_03.yaml";
    const FILE_ERROR = self::FIXTURE_DIR . "/parser_files_error.yaml";

    /**
     * @expectedException sndsgd\yaml\ParserException
     * @expectedExceptionMessage (line 8, column 1)
     */
    public function testGetErrorMessageSingle(): void
    {
        (new Parser())->parseFiles([self::FILE_ERROR]);
    }

    /**
     * @expectedException sndsgd\yaml\ParserException
     * @expectedExceptionMessage parser_files_error.yaml, line 8, column 1)
     */
    public function testGetErrorMessage02(): void
    {
        (new Parser())->parseFiles([self::FILE_ERROR, self::FILE_01]);
    }

    /**
     * @expectedException sndsgd\yaml\ParserException
     * @expectedExceptionMessage parser_files_error.yaml, line 8, column 1)
     */
    public function testGetErrorMessage03(): void
    {
        (new Parser())->parseFiles([self::FILE_01, self::FILE_ERROR]);
    }

    /**
     * @expectedException sndsgd\yaml\ParserException
     * @expectedExceptionMessage parser_files_error.yaml, line 8, column 1)
     */
    public function testGetErrorMessage04(): void
    {
        (new Parser())->parseFiles([self::FILE_01, self::FILE_02, self::FILE_ERROR]);
    }
}
