<?php declare(strict_types=1);

namespace ju1ius\Luigi\Internal\ImportMap;

/**
 * @internal
 */
enum EntryType: int
{
    case Klass = 0;
    case Function = 1;
    case Constant = 2;
}
