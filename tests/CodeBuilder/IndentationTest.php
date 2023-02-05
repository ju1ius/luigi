<?php declare(strict_types=1);

namespace ju1ius\Luigi\Tests\CodeBuilder;

use ju1ius\Luigi\CodeBuilder;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IndentationTest extends TestCase
{
    public function testIndent(): void
    {
        $code = CodeBuilder::create()->indent()->write('foo');
        Assert::assertSame('    foo', (string)$code);
    }

    public function testDedent(): void
    {
        $code = CodeBuilder::create(1)
            ->writeln('foo')
            ->dedent()
            ->write('bar');
        Assert::assertSame("    foo\nbar", (string)$code);
    }

    #[DataProvider('indentationProvider')]
    public function testIndentation(CodeBuilder $code, string $expected): void
    {
        Assert::assertSame($expected, (string)$code);
    }

    public static function indentationProvider(): iterable
    {
        yield 'raw() does not use indentation' => [
            CodeBuilder::create()->indent()->raw('foo'),
            'foo',
        ];
        yield 'write() uses indentation' => [
            CodeBuilder::create()->indent()->write('foo'),
            '    foo',
        ];
        yield 'writeln() uses indentation' => [
            CodeBuilder::create()->indent()->writeln('foo'),
            "    foo\n",
        ];
        yield 'dedent() decreases indentation' => [
            CodeBuilder::create()->indent()->writeln('a')->dedent()->writeln('b'),
            "    a\nb\n",
        ];
        yield 'indentation cannot be negative' => [
            CodeBuilder::create()->indent(-2)->writeln('a')->dedent(24)->writeln('b'),
            "a\nb\n",
        ];
    }
}
