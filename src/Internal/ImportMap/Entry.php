<?php declare(strict_types=1);

namespace ju1ius\Luigi\Internal\ImportMap;

/**
 * @internal
 */
final class Entry implements \Stringable
{
    public readonly string $name;

    public function __construct(
        public readonly EntryType $type,
        string $name,
        public readonly string $alias = '',
    ) {
        $this->name = trim($name, '\\');
    }

    public static function compare(self $a, self $b): int
    {
        if ($cmp = $a->type->value <=> $b->type->value) {
            return $cmp;
        }
        return $a->name <=> $b->name;
    }

    public function __toString(): string
    {
        return sprintf(
            'use %s%s%s;',
            match ($this->type) {
                EntryType::Function => 'function ',
                EntryType::Constant => 'const ',
                EntryType::Klass => '',
            },
            $this->name,
            $this->alias ? " as {$this->alias}" : '',
        );
    }
}
