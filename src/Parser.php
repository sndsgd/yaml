<?php

namespace sndsgd\yaml;

/**
 * A YAML parser that adds some functionality on top of the YAML extension
 *
 * Note: this is not intended to be used as part of processing live requests, but
 * rather for build or compile steps.
 */
class Parser
{
    /**
     * A regex used to verify tags when processing tag callbacks
     *
     * @var string
     */
    const TAG_REGEX = "/^![a-zA-Z0-9_]+$/";

    /**
     * A regex used to match the line and column information in error messages
     *
     * @var string
     */
    const ERROR_REGEX = "/\\(line (\d+), column (\d+)\\)/";

    /**
     * The context to use when executing YAML callbacks
     *
     * @var ParserContext
     */
    private $context;

    /**
     * Parse tags and the callables that are used to process them
     *
     * When YAML is parsed, we'll register a method on this instance as
     * the only tag callback, and then use the tagname to lookup the
     * classname of the object to create and call `execute()`.
     *
     * @see https://secure.php.net/manual/en/yaml.callbacks.parse.php
     * @var array<string,callable>
     */
    private $parseCallbacks = [];

    /**
     * The callback tags and their associated instances
     *
     * @var array<string,\sndsgd\yaml\callback\CallbackInterface>
     */
    private $parseCallbackInstances = [];


    public function __construct(
        ParserContext $context = null,
        \sndsgd\yaml\callback\CallbackInterface ...$callbacks
    )
    {
        # ensure the yaml extension is installed
        if (!extension_loaded("yaml")) {
            throw new \RuntimeException(
                "the YAML extension must be installed to use ".__CLASS__
            );
        }

        $this->context = $context ?? new ParserContext();

        foreach ($callbacks as $callback) {
            foreach ($callback->getTags() as $tag) {
                $this->parseCallbacks[$tag] = [$this, "executeTagCallback"];
                $this->parseCallbackInstances[$tag] = $callback;
            }
        }
    }

    /**
     * Create a copy of this parser with a given context
     *
     * @param ParserContext $context The context to use when parsing
     * @return Parser
     */
    public function withContext(ParserContext $context): Parser
    {
        $ret = clone $this;
        $ret->context = $context;
        return $ret;
    }

    /**
     * Parse a string of YAML
     *
     * @param string $yaml The YAML to parse
     * @param int $maxDocuments The max number of documents, or `0` for unlimited
     * @param int $prependedLines The number of lines prepended to the contents
     * @return mixed
     */
    public function parse(string $yaml, int $maxDocuments = 1, int $prependedLines = 0)
    {
        if ($maxDocuments < 0) {
            throw new \UnexpectedValueException(
                "'maxDocuments' must be greater than or equal to 0"
            );
        }

        if ($prependedLines < 0) {
            throw new \UnexpectedValueException(
                "'prependedLines' must be greater than or equal to 0"
            );
        }

        # this will be updated by `yaml_parse()` to contain the number
        # of documents found while parsing
        $documentCount = 0;

        # use a custom error handler so we can customize any error messages
        set_error_handler([$this, "handleYamlParseError"]);
        $result = yaml_parse($yaml, -1, $documentCount, $this->parseCallbacks);
        restore_error_handler();

        # verify the max number of documents is not exceeded
        if ($maxDocuments > 0 && $documentCount > $maxDocuments) {
            throw new \Exception(
                "$documentCount documents encountered; ".
                "expecting no more than $maxDocuments"
            );
        }

        return ($maxDocuments === 1) ? $result[0] : $result;
    }

    /**
     * Read a file, assume its contents are YAML, and parse
     *
     * @param string $path The absolute path to the file to parse
     * @param int $maxDocuments The max number of documents, or `0` for unlimited
     * @return mixed
     */
    public function parseFile(string $path, int $maxDocuments = 1)
    {
        $file = \sndsgd\Fs::file($path);
        $yaml = $file->read();
        if ($yaml === false) {
            throw new \Exception("failed to parse YAML file; ".$file->getError());
        }

        return $this->parse($yaml, $maxDocuments);
    }

    /**
     * Create and execute a tag callback
     *
     * @param mixed $value The value provided with the tag
     * @param string $tag The tag that maps to the callback to execute
     * @param int $flags The flags to be used
     * @return mixed The result of the callback
     * @throws ParserException When an unknown tag is encountered
     */
    public function executeTagCallback($value, string $tag, int $flags)
    {
        if (\sndsgd\Str::endsWith($tag, "/defer")) {
            $isDeferable = true;
            $tag = substr($tag, 0, -6);
        } else {
            $isDeferable = false;
        }

        $callback = $this->parseCallbackInstances[$tag] ?? "";
        if (empty($callback)) {
            if (empty($value)) {
                return $tag;
            }

            throw new ParserException("unknown callback '$tag'");
        }

        if ($isDeferable) {
            return new \sndsgd\yaml\callback\DeferrableCallback($callback, $tag, $value, $flags);
        }

        return $callback->execute($tag, $value, $flags, $this->context);
    }

    /**
     * A custom error handler to use whenever a parse error occurs
     *
     * @param int $code The error code
     * @param string $message The error message
     * @param string $file The file the error was encountered in
     * @param int $line The line the error was encountered on
     * @param array $definedVariables The variables defined when the error was encountered
     * @return void
     * @throws \ParserException
     */
    public function handleYamlParseError(
        int $code,
        string $message,
        string $file,
        int $line,
        array $definedVariables
    )
    {
        # strip the function name from the beginning of the error message
        $search = "yaml_parse(): ";
        $pos = strpos($message, $search);
        if ($pos === 0) {
            $message = substr($message, strlen($search));
        }

        # attempt to update the lines in the error message to reflect
        # the number of lines that were prepended
        $prependedLines = $definedVariables["prependedLines"] ?? 0;
        if (
            $prependedLines > 0 &&
            preg_match_all(self::ERROR_REGEX, $message, $matches, PREG_SET_ORDER)
        ) {
            foreach ($matches as list($search, $line, $column)) {
                $line -= $prependedLines;
                $replace = "(line $line, column $column)";
                $message = str_replace($search, $replace, $message);
            }
        }

        throw new ParserException($message);
    }
}
