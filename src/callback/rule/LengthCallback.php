<?php

namespace sndsgd\yaml\callback\rule;

class LengthCallback implements \sndsgd\yaml\callback\CallbackInterface
{
    private const TAG = "!rule/length";
    private const DEFAULTS = [
        "rule" => \sndsgd\rule\MinRule::class,
    ];

    public function getTags(): array
    {
        return [self::TAG];
    }

    public function execute(
        string $tag,
        $value,
        int $flags,
        \sndsgd\yaml\ParserContext $context
    )
    {
        \sndsgd\yaml\CallbackHelper::verifyTag($tag, $this->getTags());

        if (is_int($value) && $value >= 0) {
            return array_merge(self::DEFAULTS, ["length" => $value]);
        }

        if (is_array($value)) {
            \sndsgd\yaml\CallbackHelper::ensureKeysAreNotSet($value, $tag, "rule");
            return array_merge(self::DEFAULTS, $value);
        }

        throw new \sndsgd\yaml\ParserException(
            "failed to create a length rule object; expecting the length value as an " .
            "integer, or an object that contains rule properties"
        );
    }
}
