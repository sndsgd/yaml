<?php declare(strict_types=1);

namespace sndsgd\yaml;

use PHPUnit\Framework\TestCase;

class FileLocatorTest extends TestCase
{
    /**
     * @dataProvider provideFindFile
     */
    public function testFindFiles(
        array $searchPaths,
        array $excludePaths,
        array $expectErrors,
        array $expectPaths,
    ): void {
        $locator = new FileLocator(new Parser());
        [$paths, $errors] = $locator->findFiles($searchPaths, $excludePaths);

        asort($expectErrors);
        sort($expectPaths);
        sort($paths);

        $this->assertSame($expectErrors, $errors);
        $this->assertSame($expectPaths, $paths);
    }

    public function provideFindFile(): iterable
    {
        yield [
            [TESTS_DIR . "/fixtures"],
            [],
            [],
            [
                TESTS_DIR . "/fixtures/1-doc.yaml",
                TESTS_DIR . "/fixtures/2-doc.yaml",
                TESTS_DIR . "/fixtures/known-error.yaml",
                TESTS_DIR . "/fixtures/sub/3-doc.yml",
            ],
        ];

        yield "empty exclude path is ignored" => [
            [TESTS_DIR . "/fixtures"],
            [" "],
            [],
            [
                TESTS_DIR . "/fixtures/1-doc.yaml",
                TESTS_DIR . "/fixtures/2-doc.yaml",
                TESTS_DIR . "/fixtures/known-error.yaml",
                TESTS_DIR . "/fixtures/sub/3-doc.yml",
            ],
        ];

        yield [
            [TESTS_DIR . "/fixtures"],
            ["fixtures/sub", "known-error"],
            [],
            [
                TESTS_DIR . "/fixtures/1-doc.yaml",
                TESTS_DIR . "/fixtures/2-doc.yaml",
            ],
        ];

        yield [
            [TESTS_DIR . "/fixtures"],
            ["fixtures"],
            [],
            [],
        ];

        yield "test provided file path and errors" => [
            [
                TESTS_DIR . "/fixtures/1-doc.yaml",
                TESTS_DIR . "/fixtures/nope",
            ],
            [],
            [
                [
                    "path" => TESTS_DIR . "/fixtures/nope",
                    "message" => "path does not exist",
                ],
            ],
            [
                TESTS_DIR . "/fixtures/1-doc.yaml",
            ],
        ];
    }

    /**
     * @dataProvider provideFindDocuments
     */
    public function testFindDocuments(
        array $searchPaths,
        array $excludePaths,
        array $expectErrors,
        array $expectDocs,
    ): void {
        $locator = new FileLocator(new Parser());

        [$docs, $errors] = $locator->findDocuments($searchPaths, $excludePaths);

        $results = [];
        foreach ($docs as $doc) {
            $results[] = [
                "path" => $doc->path,
                "index" => $doc->index,
                "doc" => $doc->doc,
            ];
        }

        sort($results);
        sort($expectDocs);

        $this->assertSame($expectErrors, $errors);
        $this->assertSame($expectDocs, $results);
    }

    public function provideFindDocuments(): iterable
    {
        yield [
            [TESTS_DIR . "/fixtures/sub"],
            [],
            [],
            [
                [
                    "path" => TESTS_DIR . "/fixtures/sub/3-doc.yml",
                    "index" => 0,
                    "doc" => ["one" => 1],
                ],
                [
                    "path" => TESTS_DIR . "/fixtures/sub/3-doc.yml",
                    "index" => 1,
                    "doc" => ["two" => 2],
                ],
                [
                    "path" => TESTS_DIR . "/fixtures/sub/3-doc.yml",
                    "index" => 2,
                    "doc" => ["three" => 3],
                ],
            ],
        ];

        yield [
            [TESTS_DIR . "/fixtures"],
            ["3-doc"],
            [
                [
                    "path" => TESTS_DIR . "/fixtures/known-error.yaml",
                    "message" => "scanning error encountered during parsing: mapping values are not allowed in this context (line 2, column 9)",
                ],
            ],
            [
                [
                    "path" => TESTS_DIR . "/fixtures/1-doc.yaml",
                    "index" => 0,
                    "doc" => ["one" => 1],
                ],
                [
                    "path" => TESTS_DIR . "/fixtures/2-doc.yaml",
                    "index" => 0,
                    "doc" => ["one" => 1],
                ],
                [
                    "path" => TESTS_DIR . "/fixtures/2-doc.yaml",
                    "index" => 1,
                    "doc" => ["two" => 2],
                ],
            ],
        ];
    }

    public function testDocumentCreateException(): void
    {
        eval(
            <<<PHP
            class TestDocument extends sndsgd\yaml\Document
            {
                public static function create(
                    string \$path,
                    int \$index,
                    array \$doc,
                ): self {
                    throw new Exception("it did not work!");
                }
            }
            PHP
        );

        $locator = new FileLocator(new Parser());
        [, $errors] = $locator->findDocuments(
            [TESTS_DIR],
            ["2-doc", "3-doc", "known-error"],
            "TestDocument",
        );

        $this->assertSame(
            [
                [
                    "path" => TESTS_DIR . "/fixtures/1-doc.yaml document#0",
                    "message" => "it did not work!",
                ],
            ],
            $errors,
        );
    }
}
