<?php

namespace sndsgd\yaml\callback\type;

/**
 * A callback that creates an integer object that contains a `type`, `min`, and `max`
 */
class TextCallback implements \sndsgd\yaml\callback\CallbackInterface
{
    /**
     * The lengths for various types of text columns
     *
     * @var array<string,string>
     */
    const LENGTHS = [
        "!type/tinytext" => "255",
        "!type/text" => "65535",
        "!type/mediumtext" => "16777215",
        "!type/longtext" => "4294967295",
    ];

    /**
     * @inheritDoc
     */
    public function getTags(): array
    {
        return array_keys(self::LENGTHS);
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
            \sndsgd\yaml\CallbackHelper::ensureKeysAreNotSet($value, $tag, "type", "length");
        } elseif (is_string($value) && trim($value) === "") {
            $value = [];
        } else {
            throw new \sndsgd\yaml\ParserException(
                "failed to convert '$tag' to a string object; expecting the ".
                "tag without any content, or with an object of values to merge"
            );
        }

        return array_merge(
            ["type" => "string", "length" => self::LENGTHS[$tag]],
            $value
        );
    }
}
