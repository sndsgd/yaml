# sndsgd/yaml

YAML parsing enhancements for PHP.

### Context

The `sndsgd\yaml\ParserContext` object is currently unused, but has been created to ensure compatibility moving forward.

### Callbacks

You can define your own callbacks by implementing [`sndsgd\yaml\Callback`](./src/Callback.php). This repository contains an example: [`sndsgd\yaml\callbacks\SecondsCallback`](./src/callbacks/SecondsCallback.php).

### Parsing

1. Create a parser instance with all your callbacks

    ```php
    $parser = new sndsgd\yaml\Parser(
        new sndsgd\yaml\ParserContext(),
        sndsgd\yaml\callbacks\SecondsCallback::class,
        your\fun\CoolCallback::class,
    );
    ```

1. Parse strings with `->parse(string $yaml, int $maxDocuments)`
1. Parse files with `->parseFile(string $path, int $maxDocuments)`
