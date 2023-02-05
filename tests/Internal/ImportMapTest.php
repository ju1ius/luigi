<?php declare(strict_types=1);

namespace ju1ius\Luigi\Tests\Internal;

use ju1ius\Luigi\Exception\ImportError;
use ju1ius\Luigi\Internal\ImportMap;
use ju1ius\Luigi\Internal\ImportMap\Entry;
use ju1ius\Luigi\Internal\ImportMap\EntryType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ImportMapTest extends TestCase
{
    #[DataProvider('importErrorWithExistingEntryProvider')]
    public function testImportErrorWithExistingEntry(Entry $first, Entry $conflicting): void
    {
        $map = new ImportMap();
        $map->add($first);
        $this->expectException(ImportError::class);
        $map->add($conflicting);
    }

    public static function importErrorWithExistingEntryProvider(): iterable
    {
        yield 'conflicting classes' => [
            new Entry(EntryType::Klass, 'Foo\\Bar', 'Baz'),
            new Entry(EntryType::Klass, 'Foo\\Bar', 'Qux'),
        ];
        yield 'conflicting functions' => [
            new Entry(EntryType::Function, 'Foo\\Bar', 'baz'),
            new Entry(EntryType::Function, 'Foo\\Bar', 'qux'),
        ];
        yield 'conflicting constants' => [
            new Entry(EntryType::Constant, 'Foo\\BAR', 'BAZ'),
            new Entry(EntryType::Constant, 'Foo\\BAR', 'QUX'),
        ];
        yield 'ignores leading \\' => [
            new Entry(EntryType::Klass, '\\Foo\\Bar', 'Baz'),
            new Entry(EntryType::Klass, 'Foo\\Bar', 'Qux'),
        ];
    }
}
