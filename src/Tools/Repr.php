<?php declare(strict_types=1);

namespace ju1ius\Luigi\Tools;

final class Repr
{
    public static function any(array|int|float|bool|string|null $value): string
    {
        if (\is_array($value)) {
            return self::array($value);
        }
        return self::scalar($value);
    }

    public static function scalar(int|float|bool|string|null $value): string
    {
        return match (true) {
            \is_null($value) => 'null',
            \is_string($value) => self::string($value),
            \is_bool($value) => self::bool($value),
            \is_int($value) => self::int($value),
            \is_float($value) => self::float($value),
        };
    }

    public static function bool(bool $value): string
    {
        return $value ? 'true' : 'false';
    }

    public static function int(int $value, int $base = 10): string
    {
        return match ($value) {
            0 => '0',
            default => match ($base) {
                2 => sprintf('0b%02b', $value),
                8 => sprintf('0o%02o', $value),
                10 => (string)$value,
                16 => sprintf('0x%02X', $value),
            },
        };
    }

    public static function float(float $value): string
    {
        return (string)$value;
    }

    public static function string(string $value): string
    {
        if (!$value || ctype_print($value)) {
            return var_export($value, true);
        }
        $output = '';
        for ($i = 0; $i < \strlen($value); $i++) {
            $c = $value[$i];
            $o = \ord($c);
            if ($o <= 0x1F || $o >= 0x7F) {
                $output .= match ($o) {
                    0x09 => '\t',
                    0x0A => '\n',
                    0x0B => '\v',
                    0x0C => '\f',
                    0x0D => '\r',
                    default => sprintf('\x%02X', $o),
                };
            } else {
                $output .= match ($c) {
                    '\\' => '\\\\',
                    '"' => '\"',
                    '$' => '\$',
                    default => $c,
                };
            }
        }
        return '"' . $output . '"';
    }

    public static function array(array $value): string
    {
        $output = '[';
        if (array_is_list($value)) {
            $output .= implode(', ', array_map(self::any(...), $value));
        } else {
            $items = [];
            foreach ($value as $k => $v) {
                $items[] = self::scalar($k) . ' => ' . self::any($v);
            }
            $output .= implode(', ', $items);
        }
        return $output . ']';
    }

    public static function regexp(string $pattern): string
    {
        $output = strtr($pattern, ['\\\\' => '\\\\\\\\']);
        return sprintf("'%s'", addcslashes($output, "'"));
    }
}
