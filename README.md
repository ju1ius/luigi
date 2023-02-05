# ju1ius/luigi

Need to generate PHP code? Luigi does the plumbing!

## Installation

```sh
composer require ju1ius/luigi
```

## Basic usage

```php
use ju1ius\Luigi\CodeBuilder;

$code = CodeBuilder::create();
// The `raw` method adds verbatim code
$code->raw("return [\n");
// The `indent` method increases the indent level
$code->indent();
// The `write` method adds verbatim code but respects the indent level
$code->write("42,\n");
// The `writeln` method does the same as `write`, but adds a newline character after each argument.
$code->writeln('33,', '66,');
// The `dedent` method decreases the indent level
$code->dedent();
$code->writeln('];');
// CodeBuilder implements the `Stringable` interface
echo $code;
```

This is the expected output:
```php
return [
    42,
    33,
    66,
];
```
