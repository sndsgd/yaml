<?php

namespace sndsgd\yaml\callback;

use \sndsgd\yaml\ParserException;

/**
 * Convert a human readable time into the equivalent number of seconds
 *
 * Examples:
 *   !seconds 1 hour == 3600
 *   !seconds 1 day == 86400
 */
class SecondsCallback implements CallbackInterface
{
    private const TAG = "!seconds";
    private const ERR_MSG = "failed to convert %s to seconds; " .
        "expecting a human readable amount of time as a string";

    /**
     * @inheritDoc
     */
    public function getTags(): array
    {
        return [self::TAG];
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
        \sndsgd\yaml\CallbackHelper::verifyTag($tag, $this->getTags());

        if (!is_string($value)) {
            throw new ParserException(sprintf(self::ERR_MSG, "a non string value"));
        }

        if (empty($value)) {
            throw new ParserException(sprintf(self::ERR_MSG, "an empty value"));
        }

        $now = time();
        $then = strtotime("+" . $value, $now);
        if ($then === false) {
            throw new ParserException(sprintf(self::ERR_MSG, "'$value'"));
        }

        return $then - $now;
    }
}
