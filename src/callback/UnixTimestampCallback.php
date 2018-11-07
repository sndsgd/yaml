<?php

namespace sndsgd\yaml\callback;

class UnixTimestampCallback extends CallbackAbstract
{
    /**
     * @inheritDoc
     */
    public function getTags(): array
    {
        return ['!unix_timestamp'];
    }

    /**
     * @inhertiDoc
     */
    public function execute(
        string $tag,
        $value,
        int $flags,
        \sndsgd\yaml\ParserContext $context
    )
    {
        if (is_array($value)) {
            static::ensureKeysAreNotSet($value, $tag, "type", "min", "max");
            $value = $value;
        } elseif (is_string($value) && trim($value) === "") {
            $value = [];
        } else {
            throw new \sndsgd\yaml\ParserException(
                "failed to convert '$tag' to an integer object; expecting the ".
                "tag without any content, or with an object of values to merge"
            );
        }

        if (!isset($value["comment"])) {
            $value["comment"] = "unix timestamp";
        }

        return array_merge(
            ["type" => "integer", "min" => "0", "max" => "4294967295"],
            $value
        );
    }
}
