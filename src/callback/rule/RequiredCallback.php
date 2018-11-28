<?php

namespace sndsgd\yaml\callback\rule;

class RequiredCallback implements \sndsgd\yaml\callback\CallbackInterface
{
    private const TAG = "!rule/required";
    private const DEFAULTS = [
        "rule" => \sndsgd\rule\RequiredRule::class,
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
    ): array
    {
        \sndsgd\yaml\CallbackHelper::verifyTag($tag, $this->getTags());

        # if the value is an empty string
        if ($value === "") {
            return self::DEFAULTS;
        }

        if (is_array($value)) {
            \sndsgd\yaml\CallbackHelper::ensureKeysAreNotSet($value, $tag, "rule");
            return array_merge(self::DEFAULTS, $value);
        }

        throw new \sndsgd\yaml\ParserException(
            "failed to create a required rule object; expecting either an empty " .
            "string or an object with additional rule properties"
        );
    }
}
