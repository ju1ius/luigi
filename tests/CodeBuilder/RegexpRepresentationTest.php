<?php declare(strict_types=1);

namespace ju1ius\Luigi\Tests\CodeBuilder;

use ju1ius\Luigi\CodeBuilder;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RegexpRepresentationTest extends TestCase
{
    #[DataProvider('reprProvider')]
    public function testRepr(string $input, string $expected): void
    {
        $code = CodeBuilder::create()->regexp($input);
        Assert::assertSame("'{$expected}'", (string)$code);
    }

    public static function reprProvider(): iterable
    {
        yield [
            '/\x00\x0F\n/',
            '/\x00\x0F\n/',
        ];
        yield [
            '/\\./',
            '/\./',
        ];
        yield [
            '/\\\./',
            '/\\\\\\\\./',
        ];
        yield [
            '/\\\\./',
            '/\\\\\\\\./',
        ];
    }
}
