# sndsgd/yaml

[![Latest Version](https://img.shields.io/github/release/sndsgd/yaml.svg?style=flat-square)](https://github.com/sndsgd/yaml/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/sndsgd/yaml/LICENSE)
[![Build Status](https://img.shields.io/github/workflow/status/sndsgd/yaml/build?style=flat-square)](https://github.com/sndsgd/yaml/actions?query=workflow%3Abuild+branch%3Amaster)

YAML parsing enhancements for PHP.

### Context

The `sndsgd\yaml\ParserContext` object is a data bag you can jam things into for use in callbacks.

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
