<?php

namespace sndsgd\yaml\callback;

/**
 * Convert a human readable time into the equivalent number of seconds
 *
 * Examples:
 *   !seconds 1 hour == 3600
 *   !seconds 1 day == 86400
 */
class SecondsCallback extends CallbackAbstract
{
    /**
     * @inheritDoc
     */
    public function getTags(): array
    {
        return ['!seconds'];
    }

    /**
     * @inheritDoc
     */
    public function execute(
        string $tag,
        $value,
        int $flags,
        \sndsgd\yaml\ParserContext $context
    )
    {
        if (!is_string($value) || empty($value)) {
            throw new \sndsgd\yaml\ParserException(
                "failed to convert '$this->tag' to seconds; expecting the ".
                "tag followed by a human readable amount of time"
            );
        }

        $now = time();
        $then = strtotime("+".$value, $now);
        if ($then === false) {
            throw new \sndsgd\yaml\ParserException(
                "failed to convert '$this->value' to seconds"
            );
        }

        return $then - $now;
    }
}
