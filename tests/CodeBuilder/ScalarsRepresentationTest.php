<?php declare(strict_types=1);

namespace ju1ius\Luigi\Tests\CodeBuilder;

use ju1ius\Luigi\CodeBuilder;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ScalarsRepresentationTest extends TestCase
{
    #[DataProvider('stringRepresentationProvider')]
    public function testStringRepresentation(string $input, string $expected): void
    {
        $code = CodeBuilder::create()->string($input);
        Assert::assertSame($expected, (string)$code);
    }

    public static function stringRepresentationProvider(): iterable
    {
        yield 'empty string' => ['', "''"];
        yield 'printable ASCII' => ['foo bar', "'foo bar'"];
        yield 'printable w/ $' => ['foo $bar', '\'foo $bar\''];
        yield 'PHP control chars' => ["\t\n\v\f\r", '"\t\n\v\f\r"'];
        yield 'escapes quotes' => ["\t\"foo\"bar", '"\t\"foo\"bar"'];
        yield 'escapes $and backslash' => ["\t\\foo \$bar", '"\t\\\\foo \$bar"'];
        yield 'escapes bytes' => ["\x00\xFF", '"\x00\xFF"'];
    }

    #[DataProvider('integerRepresentationProvider')]
    public function testIntegerRepresentation(int $input, int $base, string $expected): void
    {
        $code = CodeBuilder::create()->int($input, $base);
        Assert::assertSame($expected, (string)$code);
    }

    public static function integerRepresentationProvider(): iterable
    {
        yield '0 in binary' => [0, 2, '0'];
        yield '0 in octal' => [0, 8, '0'];
        yield '0 in hexadecimal' => [0, 16, '0'];
        yield '42 in base 10' => [42, 10, '42'];
        yield '42 in binary' => [42, 2, '0b101010'];
        yield '42 in octal' => [42, 8, '0o52'];
        yield '42 in hexadecimal' => [42, 16, '0x2A'];
    }

    #[DataProvider('genericRepresentationProvider')]
    public function testGenericRepresentation(mixed $input, string $expected): void
    {
        $code = CodeBuilder::create()->repr($input);
        Assert::assertSame($expected, (string)$code);
    }

    public static function genericRepresentationProvider(): iterable
    {
        yield 'null' => [null, 'null'];
        yield 'int' => [42, '42'];
        yield 'float' => [1.5, '1.5'];
        yield 'true' => [true, 'true'];
        yield 'false' => [false, 'false'];
        yield 'string' => ['foobar', "'foobar'"];
        yield 'list of int' => [[1, 2, 3], '[1, 2, 3]'];
        yield 'list of string' => [['a', 'b', 'c'], "['a', 'b', 'c']"];
        yield 'list of list' => [[[1, 2], [3, 4]], '[[1, 2], [3, 4]]'];
        yield 'assoc' => [
            ['a' => 1, 'b' => 2, 'c' => 4],
            "['a' => 1, 'b' => 2, 'c' => 4]",
        ];
        yield 'assoc with integer keys' => [
            [666 => 'the beast', 42 => 'everything'],
            "[666 => 'the beast', 42 => 'everything']",
        ];
    }
}
