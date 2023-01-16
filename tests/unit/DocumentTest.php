<?php declare(strict_types=1);

namespace sndsgd\yaml;

use PHPUnit\Framework\TestCase;

class YamlDocumentTest extends TestCase
{
    /**
     * @dataProvider provideGetDebugPath
     */
    public function testGetDebugPath(
        string $path,
        int $index,
        string $expect,
    ): void {
        $doc = Document::create($path, $index, []);
        $this->assertSame($expect, $doc->getDebugPath());
    }

    public function provideGetDebugPath(): iterable
    {
        yield [
            "/foo/bar/thing.yaml",
            12,
            "/foo/bar/thing.yaml document#12",
        ];

        yield [
            "/hi/bye.yaml",
            1,
            "/hi/bye.yaml document#1",
        ];
    }
}
