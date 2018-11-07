<?php

namespace sndsgd\yaml\callback;

/**
 * A callback that creates an integer object that contains a `type`, `min`, and `max`
 */
class TextCallback extends \sndsgd\yaml\callback\CallbackAbstract
{
    /**
     * The lengths for various types of text columns
     *
     * @var array<string,string>
     */
    const LENGTHS = [
        "!tinytext" => "255",
        "!text" => "65535",
        "!mediumtext" => "16777215",
        "!longtext" => "4294967295",
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
        if (is_array($this->value)) {
            static::ensureKeysAreNotSet($this->value, $this->tag, "type", "length");
            $value = $this->value;
        } elseif (is_string($this->value) && trim($this->value) === "") {
            $value = [];
        } else {
            throw new \sndsgd\yaml\ParserException(
                "failed to convert '$this->tag' to a string object; expecting the ".
                "tag without any content, or with an object of values to merge"
            );
        }

        return array_merge(
            ["type" => "string", "length" => self::LENGTHS[$this->tag]],
            $value
        );
    }
}
