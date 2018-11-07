<?php

namespace sndsgd\yaml\callback;

/**
 *
 */
interface CallbackInterface
{
    /**
     * Retrieve a list of tags the callback is capable of handling
     *
     * @return array<string>
     */
    public function getTags(): array;

    /**
     * Execute the callback action; convert the input into the desired output
     *
     * @param string $tag The tag asscoiated with the callback to execute
     * @param mixed $value The value that was included with the tag
     * @param int $flags Flags to pass along to the parser
     * @param \sndsgd\yaml\ParserContext $context The context to execute the callback with
     * @return mixed
     * @throws \sndsgd\yaml\ParserException If something unexpected happens
     */
    public function execute(
        string $tag,
        $value,
        int $flags,
        \sndsgd\yaml\ParserContext $context
    );
}
