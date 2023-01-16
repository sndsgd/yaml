<?php declare(strict_types=1);

namespace sndsgd\yaml;

use ErrorException;
use sndsgd\yaml\exceptions\DuplicateCallbackTagException;
use sndsgd\yaml\exceptions\InvalidCallbackClassException;
use sndsgd\yaml\exceptions\ParserException;
use UnexpectedValueException;

/**
 * A YAML parser that adds some functionality on top of the YAML extension
 *
 * Note: this is not intended to be used as part of processing live requests, but
 * rather for build or compile steps.
 */
class Parser
{
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
     * The callback tags and their associated callback classnames
     *
     * @var array<string,string>
     */
    private $parseCallbackClasses = [];


    public function __construct(
        ?ParserContext $context = null,
        string ...$callbackClasses,
    ) {
        $this->context = $context ?? new ParserContext();

        foreach ($callbackClasses as $callbackClass) {
            if (!in_array(Callback::class, class_implements($callbackClass), true)) {
                throw new InvalidCallbackClassException(
                    sprintf(
                        "failed to add '%s'; callback classes must implement '%s'",
                        $callbackClass,
                        Callback::class,
                    ),
                );
            }

            $tag = $callbackClass::getYamlCallbackTag();
            if (isset($this->parseCallbacks[$tag])) {
                throw new DuplicateCallbackTagException(
                    sprintf(
                        "failed to add '%s'; the tag '%s' is already in use",
                        $callbackClass,
                        $tag,
                    ),
                );
            }

            $this->parseCallbacks[$tag] = [$this, "executeTagCallback"];
            $this->parseCallbackClasses[$tag] = $callbackClass;
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
     * @return mixed
     */
    public function parse(string $yaml, int $maxDocuments = 1)
    {
        if ($maxDocuments < 0) {
            throw new UnexpectedValueException(
                "'maxDocuments' must be greater than or equal to 0",
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
            throw new ParserException(
                "$documentCount documents encountered; expecting no more than $maxDocuments",
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
    public function parseFile(
        string $path,
        int $maxDocuments = 1,
    ) {
        set_error_handler(static function (
            int $code,
            string $message,
            string $file,
            int $line,
        ) {
            throw new ErrorException(
                "parsing YAML file failed; $message",
                $code,
                E_ERROR,
                $file,
                $line,
            );
        });

        $yaml = file_get_contents($path);
        restore_error_handler();

        return $this->parse($yaml, $maxDocuments);
    }

    /**
     * Process a value using
     *
     * @param mixed $value The value provided with the tag
     * @param string $tag The tag that maps to the callback to execute
     * @param int $flags The flags to be used
     * @return mixed The result of the callback
     * @throws ParserException When an unknown tag is encountered
     */
    public function executeTagCallback($value, string $tag, int $flags)
    {
        $callbackClass = $this->parseCallbackClasses[$tag] ?? null;
        if ($callbackClass === null) {
            return $tag;
        }

        return $callbackClass::executeYamlCallback($tag, $value, $flags, $this->context);
    }

    /**
     * A custom error handler to use whenever a parse error occurs
     *
     * @param int $code The error code
     * @param string $message The error message
     * @param string $file The file the error was encountered in
     * @param int $line The line the error was encountered on
     * @return bool
     * @throws ParserException
     */
    public function handleYamlParseError(
        int $code,
        string $message,
        string $file,
        int $line,
    ): bool {
        # strip the function name from the beginning of the error message
        $search = "yaml_parse(): ";
        $pos = strpos($message, $search);
        if ($pos === 0) {
            $message = substr($message, strlen($search));
        }

        throw new ParserException($message);
    }
}
