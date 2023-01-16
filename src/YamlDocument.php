<?php declare(strict_types=1);

namespace sndsgd\yaml;

class YamlDocument
{
    public function __construct(
        public readonly string $path,
        public readonly int $index,
        public readonly array $doc,
    ) {}

    public function getDebugPath(): string
    {
        return sprintf(
            "%s document#%d",
            $this->path,
            $this->index,
        );
    }
}
