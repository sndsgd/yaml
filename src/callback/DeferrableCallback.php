<?php

namespace sndsgd\yaml\callback;

/**
 * A class that is used to store a callback for deferred execution. This can be helpful
 * when parsing multi document yaml files.
 */
class DeferrableCallback
{
    protected $callback;
    protected $tag;
    protected $value;
    protected $flags;

    public function __construct(string $tag, $value, int $flags)
    {
        $this->callback = $callback;
        $this->tag = $tag;
        $this->value = $value;
        $this->flags = $flags;
    }

    public function execute(\sndsgd\yaml\ParserContext $context)
    {
        return $this->callback->execute($this->tag, $this->value, $this->flags, $context);
    }
}
