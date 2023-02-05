<?php declare(strict_types=1);

namespace ju1ius\Luigi\Tests;

use ju1ius\Luigi\CodeBuilder;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

enum ExampleEnum
{
    case Foo;
    case Bar;
}

final class CodeBuilderTest extends TestCase
{
    public function testEach(): void
    {
        $code = CodeBuilder::create();
        $code->each(['a'], static function($v, $k, $c) use ($code) {
            Assert::assertSame('a', $v);
            Assert::assertSame(0, $k);
            Assert::assertSame($code, $c);
        });
    }

    public function testJoin(): void
    {
        $code = CodeBuilder::create()
            ->join(', ', ['a', 'b', 'c'], fn($v, $k, $c) => $c->raw((string)$k)->raw(':')->raw($v))
        ;
        Assert::assertSame('0:a, 1:b, 2:c', (string)$code);
    }

    public function testFileHeader(): void
    {
        $code = CodeBuilder::forFile()->writeln('$foo = 42;');
        $expected = <<<'PHP'
        <?php declare(strict_types=1);

        $foo = 42;

        PHP;
        Assert::assertSame($expected, (string)$code);
    }

    public function testClassName(): void
    {
        $code = CodeBuilder::create()
            ->className('\Foo\Bar')->raw("\n")
            ->className('\Baz', false)->raw("\n")
            ->className('finfo')->raw("\n")
        ;
        $expected = <<<'EOS'
        use Foo\Bar;
        use finfo;

        Bar
        \Baz
        finfo

        EOS;
        Assert::assertSame($expected, (string)$code);
    }

    public function testNew(): void
    {
        $code = CodeBuilder::create()
            ->new('Foo\Bar')->raw(";\n")
            ->new('Baz')->raw(";\n")
            ->new('stdClass')->raw(";\n")
        ;
        $expected = <<<'PHP'
        use Baz;
        use Foo\Bar;
        use stdClass;

        new Bar;
        new Baz;
        new stdClass;

        PHP;
        Assert::assertSame($expected, (string)$code);
    }

    #[DataProvider('enumProvider')]
    public function testEnum(\UnitEnum $input, bool $import, string $expected): void
    {
        $code = CodeBuilder::create()->enum($input, $import);
        Assert::assertSame($expected, (string)$code);
    }

    public static function enumProvider(): iterable
    {
        yield 'import' => [
            ExampleEnum::Foo,
            true,
            <<<'PHP'
            use ju1ius\Luigi\Tests\ExampleEnum;

            ExampleEnum::Foo
            PHP,
        ];
        yield 'no import' => [
            ExampleEnum::Bar,
            false,
            <<<'PHP'
            ju1ius\Luigi\Tests\ExampleEnum::Bar
            PHP,
        ];
    }
}
