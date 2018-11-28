<?php

namespace sndsgd\yaml\callback\type;

class BooleanCallback implements \sndsgd\yaml\callback\CallbackInterface
{
    const DEFAULTS = ["type" => "boolean"];

    /**
     * @inheritDoc
     */
    public function getTags(): array
    {
        return ["!type/boolean"];
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

        if ($value === "") {
            return self::DEFAULTS;
        }

        if (is_array($value)) {
            \sndsgd\yaml\CallbackHelper::ensureKeysAreNotSet($value, $tag, "type");
            return array_merge(self::DEFAULTS, $value);
        }

        throw new \sndsgd\yaml\ParserException(
            "failed to convert '$tag' to a boolean object; expecting the ".
            "tag without any content, or with an object of values to merge"
        );
    }
}
