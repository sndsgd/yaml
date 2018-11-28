<?php

namespace sndsgd\yaml\callback\rule;

class MinimumCallback implements \sndsgd\yaml\callback\CallbackInterface
{
    private const TAG = "!rule/min";
    private const DEFAULTS = [
        "rule" => \sndsgd\rule\MinRule::class,
    ];

    private const ERROR_MESSAGE = "failed to create a minimum rule object; expecting " .
        "the min value as an integer/float, or an object that contains rule properties";

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

        if (is_array($value)) {
            \sndsgd\yaml\CallbackHelper::ensureKeysAreNotSet($value, $tag, "rule");
            if (isset($value["min"])) {
                $value["min"] = (string) $value["min"];
            }
            return array_merge(self::DEFAULTS, $value);
        }

        if (is_numeric($value)) {
            return array_merge(self::DEFAULTS, ["min" => (string) $value]);
        }

        throw new \sndsgd\yaml\ParserException(self::ERROR_MESSAGE);
    }
}
