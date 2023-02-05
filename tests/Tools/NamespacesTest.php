<?php declare(strict_types=1);

namespace ju1ius\Luigi\Tests\Tools;

use ju1ius\Luigi\Tools\Namespaces;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NamespacesTest extends TestCase
{
    #[DataProvider('splitProvider')]
    public function testRemove(string $name, array $expected): void
    {
        $result = Namespaces::remove($name);
        [, $tail] = $expected;
        Assert::assertSame($tail, $result);
    }

    #[DataProvider('splitProvider')]
    public function testSplit(string $name, array $expected): void
    {
        $result = Namespaces::split($name);
        Assert::assertSame($expected, $result);
    }

    public static function splitProvider(): iterable
    {
        yield 'no namespace' => [
            'Foo',
            ['', 'Foo'],
        ];
        yield 'no namespace w/ leading \\' => [
            '\\Foo',
            ['', 'Foo'],
        ];
        yield 'namespaced name' => [
            'Foo\\Bar\\Baz',
            ['Foo\\Bar', 'Baz'],
        ];
    }
}
