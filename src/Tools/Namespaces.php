<?php declare(strict_types=1);

namespace ju1ius\Luigi\Tools;

final class Namespaces
{
    public static function remove(string $name): string
    {
        [, $tail] = self::split($name);
        return $tail;
    }

    /**
     * @return array{string, string}
     */
    public static function split(string $name): array
    {
        return match ($p = strrpos($name, '\\')) {
            false => ['', $name],
            default => [
                substr($name, 0, $p),
                substr($name, $p + 1),
            ],
        };
    }
}
