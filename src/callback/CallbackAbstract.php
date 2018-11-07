<?php

namespace sndsgd\yaml\callback;

abstract class CallbackAbstract implements CallbackInterface
{
    /**
     * Verify a tag can be handled by the callback
     *
     * @param string $tag The tag to verify
     * @return string
     */
    protected function verifyTag(string $tag): string
    {
        $tags = $this->getTags();
        if (!in_array($tag, $tags, true)) {
            throw new \UnexpectedValueException(sprintf(
                "invalid tag '%s'; '%s' can handle %s: %s",
                $this->tag,
                get_class($this),
                count($tags) === 1 ? "one tag" : "these tags",
                implode(", ", $tags)
            ));
        }

        return $tag;
    }

    /**
     * Verify that none of the provided keys already exist in the values
     * This can be used to ensure the result of the callback will not overwrite
     * any content that already exists in the value
     *
     * @param array<string,mixed> $value The value to check for keys in
     * @param string ...$keys The keys to check
     * @return void
     * @throws \LogicException If `$this->values` is not an array
     * @throws \sndsgd\yaml\ParserException If any of the keys exist
     */
    protected static function ensureKeysAreNotSet(
        array $value,
        string $tag,
        string ...$keys
    ): void
    {
        $tmp = [];
        foreach ($keys as $key) {
            if (isset($value[$key])) {
                $tmp[] = $key;
            }
        }

        $len = count($tmp);
        if ($len === 0) {
            return;
        }

        $noun = ($len === 1) ? "key" : "keys";
        throw new \sndsgd\yaml\ParserException(
            "failed to process callback '$tag'; the following $noun ".
            "would be overwritten: ".implode(", ", $tmp)
        );
    }
}
