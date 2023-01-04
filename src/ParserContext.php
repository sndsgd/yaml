<?php declare(strict_types=1);

namespace sndsgd\yaml;

use LogicException;

class ParserContext
{
    private array $values = [];

    public function set(
        string $key,
        mixed $value,
    ): void {
        $this->values[$key] = $value;
    }

    public function get(
        string $key,
    ): mixed {
        if (!isset($this->values[$key])) {
            throw new LogicException(
                "failed to retrieve '$key' from " . self::class,
            );
        }

        return $this->values[$key];
    }
}
