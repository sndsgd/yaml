<?php declare(strict_types=1);

namespace sndsgd\yaml\callbacks;

use sndsgd\yaml\Callback;
use sndsgd\yaml\exceptions\ParserException;
use sndsgd\yaml\ParserContext;

/**
 * Convert a human readable time into the equivalent number of seconds
 *
 * Examples:
 *   !seconds 1 hour == 3600
 *   !seconds 1 day == 86400
 */
class SecondsCallback implements Callback
{
    private const ERR_MSG = "failed to convert %s to seconds; "
        . "expecting a human readable amount of time as a string";

    public static function getYamlCallbackTag(): string
    {
        return "!seconds";
    }

    /**
     * @inheritDoc
     */
    public static function executeYamlCallback(
        string $tag,
        $value,
        int $flags,
        ParserContext $context
    ) {
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
