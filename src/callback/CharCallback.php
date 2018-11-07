<?php

namespace sndsgd\yaml\callback;

class CharCallback extends CallbackAbstract
{
    /**
     * A map of tags and whether the associated string object will have a fixed length
     *
     * @var array<string,bool>
     */
    const TAGS = [
        '!varchar' => false,
        '!char' => true,
    ];

    /**
     * @inheritDoc
     */
    public function getTags(): array
    {
        return array_keys(self::TAGS);
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
        if (is_int($value)) {
            $length = strval($value);
            $value = [];
        } elseif (
            is_string($value) &&
            ($value === "" || preg_match("/^-?[0-9]+$/", $value))
        ) {
            $length = $value ?: "255";
            $value = [];
        } elseif (is_array($value)) {
            static::ensureKeysAreNotSet($value, $tag, "type");
            $value = $value;
            $length = $value["length"] ?? "255";
            if (is_scalar($length)) {
                $length = strval($length);
            }
            unset($value["length"]);
        } else {
            throw new \sndsgd\yaml\ParserException(
                "failed to convert '$tag' to a string object; expecting the ".
                "tag without any content, or with the length as an integer, or with an ".
                "object of values to merge"
            );
        }

        $base = [
            "type" => "string",
            "length" => $length,
            "isFixed" => self::TAGS[$tag],
        ];

        return array_merge($base, $value);
    }
}
