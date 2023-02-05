<?php declare(strict_types=1);

namespace ju1ius\Luigi\Internal;

use ju1ius\Luigi\Exception\ImportError;
use ju1ius\Luigi\Internal\ImportMap\Entry;
use ju1ius\Luigi\Internal\ImportMap\EntryType;

/**
 * @internal
 */
final class ImportMap
{
    /**
     * @var Entry[]
     */
    private array $entries = [];

    public function addClass(string $name, string $alias = ''): void
    {
        $this->add(new Entry(EntryType::Klass, $name, $alias));
    }

    public function addFunction(string $name, string $alias = ''): void
    {
        $this->add(new Entry(EntryType::Function, $name, $alias));
    }

    public function addConstant(string $name, string $alias = ''): void
    {
        $this->add(new Entry(EntryType::Constant, $name, $alias));
    }

    /**
     * @return Entry[]
     */
    public function all(): array
    {
        usort($this->entries, Entry::compare(...));
        return array_values($this->entries);
    }

    public function add(Entry $entry): void
    {
        $key = self::keyOf($entry);
        if ($existing = $this->entries[$key] ?? null) {
            if (strcasecmp($entry->alias, $existing->alias) !== 0) {
                throw ImportError::conflictingAlias($entry, $existing);
            }
        }

        $this->entries[$key] = $entry;
    }

    private static function keyOf(Entry $entry): string
    {
        return sprintf(
            '%d|%s',
            $entry->type->value,
            strtolower($entry->name),
        );
    }
}
