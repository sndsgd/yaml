<?php declare(strict_types=1);

namespace sndsgd\yaml;

use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

class FileLocator
{
    public function __construct(
        private Parser $parser,
    ) {}

    public function findFiles(
        array $searchPaths,
        array $excludePaths = [],
    ): array {
        $excludeRegex = self::createExcludeRegex($excludePaths);

        $paths = [];
        $errors = [];

        foreach ($searchPaths as $searchPath) {
            if (!file_exists($searchPath)) {
                $errors[$searchPath] = "path does not exist";
            } elseif (is_dir($searchPath)) {
                foreach (self::createIterator($searchPath, $excludeRegex) as $file) {
                    $filePath = $file->getPathName();
                    $paths[$filePath] = $filePath;
                }
            } elseif (self::isYamlFile($searchPath)) {
                $paths[$searchPath] = $searchPath;
            }
        }

        return [array_values($paths), $errors];
    }

    public function findDocuments(
        array $searchPaths,
        array $excludePaths = [],
        string $documentClass = Document::class,
    ): array {
        $docs = [];

        [$paths, $errors] = $this->findFiles($searchPaths, $excludePaths);

        foreach ($paths as $path) {
            try {
                $rawDocs = $this->parser->parseFile($path, 0);
            } catch (Throwable $ex) {
                $errors[$path] = $ex->getMessage();
            }

            foreach (($rawDocs ?? []) as $index => $rawDoc) {
                try {
                    $docs[] = $documentClass::create($path, $index, $rawDoc);
                } catch (Throwable $ex) {
                    $debugPath = Document::renderDebugPath($path, $index);
                    $errors[$debugPath] = $ex->getMessage();
                }
            }
        }

        return [$docs, $errors];
    }

    public static function isYamlFile(string $path): bool
    {
        if (!is_file($path)) {
            return false;
        }

        $lcPath = strtolower($path);
        return (
            str_ends_with($lcPath, ".yaml")
            || str_ends_with($lcPath, ".yml")
        );
    }

    private static function createExcludeRegex(
        array $excludePaths,
    ): string {
        if ($excludePaths === []) {
            return "";
        }

        $groups = [];
        foreach ($excludePaths as $path) {
            $path = trim($path);
            if ($path !== "") {
                $groups[] = preg_quote($path);
            }
        }

        if ($groups === []) {
            return "";
        }

        return sprintf("~(%s)~i", implode("|", $groups));
    }

    private static function createIterator(
        string $searchDir,
        string $excludeRegex,
    ): RecursiveIteratorIterator {
        return new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator($searchDir),
                static function ($current, $key, $iterator) use ($excludeRegex) {
                    if (
                        $excludeRegex !== ""
                        && preg_match($excludeRegex, $current->getPathname())
                    ) {
                        return false;
                    }

                    return (
                        $iterator->hasChildren()
                        || FileLocator::isYamlFile($current->getPathname())
                    );
                },
            ),
        );
    }
}
