<?php declare(strict_types=1);

namespace sndsgd\yaml;

use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;
use Throwable;
use UnexpectedValueException;

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
                $errors[] = [
                    "path" => $searchPath,
                    "message" => "provided search path does not exist",
                ];
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
        $documentCreateMethod = self::verifyDocumentClass($documentClass);

        [$paths, $errors] = $this->findFiles($searchPaths, $excludePaths);
        if ($errors !== []) {
            return [[], $errors];
        }

        $docs = [];
        foreach ($paths as $path) {
            try {
                $rawDocs = $this->parser->parseFile($path, 0);
            } catch (Throwable $ex) {
                $errors[] = [
                    "path" => $path,
                    "message" => $ex->getMessage(),
                ];
                continue;
            }

            foreach ($rawDocs as $index => $rawDoc) {
                try {
                    $doc = $documentCreateMethod->invoke(
                        null,
                        $path,
                        $index,
                        $rawDoc,
                    );
                } catch (Throwable $ex) {
                    $debugPath = Document::renderDebugPath($path, $index);
                    $errors[] = [
                        "path" => $debugPath,
                        "message" => $ex->getMessage(),
                    ];
                    continue;
                }

                if ($doc !== null) {
                    $docs[] = $doc;
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

    private static function verifyDocumentClass(
        string $class,
    ): ReflectionMethod {
        if ($class === Document::class) {
            return new ReflectionMethod($class, "create");
        }

        $rc = new ReflectionClass($class);
        if (!$rc->isSubclassOf(Document::class)) {
            throw new UnexpectedValueException(
                sprintf(
                    "provided document class '%s' must extend '%s'",
                    $class,
                    Document::class,
                ),
            );
        }

        return $rc->getMethod("create");
    }
}
