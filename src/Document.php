<?php declare(strict_types=1);

namespace sndsgd\yaml;

/**
 * A representation of a single YAML document. Intended to be
 * used when dealing with lots of yaml files that have lots of
 * documents that may have issues.
 */
class Document
{
    /**
     * Create an instance of this class. We use a factory
     * method so this class and all subclasses are instantiated
     * the same way. This simplifies file location a teeny bit.
     */
    public static function create(
        string $path,
        int $index,
        array $doc,
    ): ?self {
        return new self($path, $index, $doc);
    }

    public static function renderDebugPath(
        string $path,
        int $index,
    ): string {
        return sprintf("%s document#%d", $path, $index);
    }

    private function __construct(
        public readonly string $path,
        public readonly int $index,
        public readonly array $doc,
    ) {}

    public function getDebugPath(): string
    {
        return self::renderDebugPath($this->path, $this->index);
    }
}
