# sndsgd/yaml

[![Latest Version](https://img.shields.io/github/release/sndsgd/yaml.svg?style=flat-square)](https://github.com/sndsgd/yaml/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/sndsgd/yaml/LICENSE)
[![Build Status](https://img.shields.io/travis/sndsgd/yaml/master.svg?style=flat-square)](https://travis-ci.org/sndsgd/yaml)
[![Coverage Status](https://img.shields.io/coveralls/sndsgd/yaml.svg?style=flat-square)](https://coveralls.io/r/sndsgd/yaml?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/sndsgd/yaml.svg?style=flat-square)](https://packagist.org/packages/sndsgd/yaml)

YAML parsing enhancements for PHP.


## Requirements

This project is unstable and subject to changes from release to release.

You need **PHP >= 7.1** to use this library, however, the latest stable version of PHP is recommended.


## Install

Install `sndsgd/yaml` using [Composer](https://getcomposer.org/).


## Usage


### Callbacks

Callbacks are useful for performing logic when parsing a YAML document. This library introduces an interface for defining your own custom callbacks, and then using them when parsing YAML.

_example-01.yaml_
```yaml
parameters:
  ttl: !type/uint32
    default: !seconds 1 day 42 seconds
    rules:
    - !rule/required
      error_message: fill out this value or else
```

```php
# instantiate callbacks
$callbacks = [
    new sndsgd\yaml\callback\SecondsCallback(),
    new sndsgd\yaml\callback\rule\RequiredCallback(),
    new sndsgd\yaml\callback\type\IntegerCallback(),
];

# create a new parser instance, then read and parse some yaml
$parser = new sndsgd\yaml\Parser(null, ...$callbacks);
$data = $parser->parseFile("example-01.yaml");
echo json_encode($data, sndsgd\JSON::HUMAN) . PHP_EOL;
```
```json
{
  "parameters": {
    "ttl": {
      "type": "integer",
      "min": "0",
      "max": "4294967295",
      "default": 86442,
      "rules": [
        {
          "rule": "sndsgd\\rule\\RequiredRule",
          "error_message": "fill out this value or else"
        }
      ]
    }
  }
}
```


### Multi Document Merge and Parse

Because yaml lacks import or include functionality, you may find yourself wanting to merge multiple documents together and then parse contents. A common use case for this is to define some values using YAML anchors and aliases, and then include that content when parsing another file so you have access to the predefined values.

_example-02.yaml_
```yaml
urlencoded: &urlencoded application/x-www-form-urlencoded
multipart: &multipart multipart/form-data
json: &json application/json; charset=utf-8
```

_example-03.yaml_
```yaml
url: /register
method: POST
headers:
  content-type: *json
body:
  email: foo@example.com
```

```php
$data = $parser->parseFiles(["example-02.yaml", "example-03.yaml"]);
echo json_encode($data, sndsgd\JSON::HUMAN) . PHP_EOL;
```
