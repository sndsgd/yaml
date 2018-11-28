<?php

namespace sndsgd\yaml\callback\type;

/**
 * A callback that creates an integer object that contains a `type`, `min`, and `max`
 */
class IntegerCallback implements \sndsgd\yaml\callback\CallbackInterface
{
    const UINT_32_MAX = "4294967295";

    /**
     * The min and max values for various types of integers
     * Stored as strings so we can handle unsigned 64 bit integers
     *
     * @var array<string,array<string>>
     */
    const RANGES = [
        "!type/uint8" => ["0", "255"],
        "!type/int8" => ["-128", "127"],
        "!type/uint16" => ["0", "65535"],
        "!type/int16" => ["32768", "32767"],
        "!type/uint32" => ["0", self::UINT_32_MAX],
        "!type/int32" => ["-2147483648", "2147483647"],
        "!type/uint64" => ["0", "18446744073709551615"],
        "!type/int64" => ["-9223372036854775808", "9223372036854775807"],

        "!type/unix_timestamp" => ["0", self::UINT_32_MAX],
    ];

    /**
     * @inheritDoc
     */
    public function getTags(): array
    {
        return array_keys(self::RANGES);
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

        if (is_array($value)) {
            \sndsgd\yaml\CallbackHelper::ensureKeysAreNotSet($value, $tag, "type", "min", "max");
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
