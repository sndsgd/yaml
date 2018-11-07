<?php

namespace sndsgd\yaml\callback;

/**
 * A callback that creates an integer object that contains a `type`, `min`, and `max`
 */
class IntegerCallback extends \sndsgd\yaml\callback\CallbackAbstract
{

    const DEFAULT_TAG = "!integer";

    /**
     * The min and max values for various types of integers
     * Stored as strings so we can handle unsigned 64 bit integers
     *
     * @var array<string,array<string>>
     */
    const RANGES = [
        "!uint8" => ["0", "255"],
        "!int8" => ["-128", "127"],
        "!uint16" => ["0", "65535"],
        "!int16" => ["32768", "32767"],
        "!uint32" => ["0", "4294967295"],
        "!int32" => ["-2147483648", "2147483647"],
        "!uint64" => ["0", "18446744073709551615"],
        "!int64" => ["-9223372036854775808", "9223372036854775807"],
    ];

    /**
     * @inheritDoc
     */
    public function getTags(): array
    {
        $ret = array_keys(self::RANGES);
        $ret[] = self::DEFAULT_TAG;
        return $ret;
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
        $this->verifyTag($tag);

        # if a short tag was provided, we can just use the associated min and max values
        if (isset(self::RANGES[$tag])) {
            return $this->processShortTag($tag, $value);
        }

        var_dump($value);

        # if `!integer` was use with an integer value, assume the value is the
        # max and the min will be zero. iterate through the ranges to find the
        # correct tag that can handle the max, and then process as if the short
        # tag was provided in the first place.
        if (is_string($value) && preg_match("/[0-9]+/", $value)) {
            foreach (self::RANGES as $shortTag => [$min, $max]) {
                if ($min === "0" && $value < $max) {
                    return $this->processShortTag($shortTag, []);
                }
            }

            throw new \RuntimeException("failed to process very large number");
        }

        # `!integer` was provided; expect at least a `min` and a `max`, both of
        # which must be integers
        if (!is_array($value)) {
            throw new \sndsgd\yaml\ParserException(
                "failed to convert '$tag' to an integer object; expecting an object".
                "with min and max properties"
            );
        }
    }

    private function processShortTag(string $tag, $value): array
    {
        if (is_array($value)) {
            static::ensureKeysAreNotSet($value, $tag, "type", "min", "max");
        } elseif (is_string($value) && trim($value) === "") {
            $value = [];
        } else {
            throw new \sndsgd\yaml\ParserException(
                "failed to convert '$tag' to an integer object; expecting the ".
                "tag without any content, or with an object of values to merge"
            );
        }

        [$min, $max] = self::RANGES[$tag];

        return array_merge(
            ["type" => "integer", "min" => $min, "max" => $max],
            $value
        );
    }
}
