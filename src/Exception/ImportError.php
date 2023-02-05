<?php declare(strict_types=1);

namespace ju1ius\Luigi\Exception;

use ju1ius\Luigi\Internal\ImportMap\Entry;

final class ImportError extends \LogicException implements LuigiException
{
    public static function conflictingAlias(Entry $newEntry, Entry $existingEntry): self
    {
        return new self(sprintf(
            'Entry already imported under the alias "%s": "%s"',
            $existingEntry->alias,
            $newEntry,
        ));
    }
}
