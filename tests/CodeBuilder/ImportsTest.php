<?php declare(strict_types=1);

namespace ju1ius\Luigi\Tests\CodeBuilder;

use ju1ius\Luigi\CodeBuilder;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class ImportsTest extends TestCase
{
    public function testUse(): void
    {
        $code = CodeBuilder::create()
            ->writeln('acme_qux(new Foo(WHATEVER), new Qux());')
            ->use('Foo')
            ->useConst('Foo\\WHATEVER')
            ->useFunction('Acme\\qux', 'acme_qux')
            ->use('Bar\\Baz', 'Qux')
        ;
        $expected = <<<'PHP'
        use Bar\Baz as Qux;
        use Foo;
        use function Acme\qux as acme_qux;
        use const Foo\WHATEVER;
        
        acme_qux(new Foo(WHATEVER), new Qux());

        PHP;
        Assert::assertSame($expected, (string)$code);
    }
}
