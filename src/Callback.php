<?php declare(strict_types=1);

namespace sndsgd\yaml;

interface Callback
{
    /**
     * Retrieve the tags the callback can handle
     *
     * @return string
     */
    public static function getYamlCallbackTag(): string;

    /**
     * Execute the callback action; convert the input into the desired output
     *
     * @param string $tag The tag asscoiated with the callback to execute
     * @param mixed $value The value that was included with the tag
     * @param int $flags Flags to pass along to the parser
     * @param \sndsgd\yaml\ParserContext $context The context to execute the callback with
     * @return mixed
     * @throws \sndsgd\yaml\exceptions\ParserException If something unexpected happens
     */
    public static function executeYamlCallback(
        string $tag,
        $value,
        int $flags,
        \sndsgd\yaml\ParserContext $context
    );
}
