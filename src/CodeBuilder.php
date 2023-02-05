<?php declare(strict_types=1);

namespace ju1ius\Luigi;

use ju1ius\Luigi\Internal\ImportMap;
use ju1ius\Luigi\Internal\IndentStack;
use ju1ius\Luigi\Tools\Namespaces;
use ju1ius\Luigi\Tools\Repr;
use Stringable;

final class CodeBuilder implements Stringable
{
    private string $header = '';
    private string $body = '';

    private function __construct(
        private readonly IndentStack $indentStack,
        private readonly ImportMap $imports = new ImportMap(),
    ) {
    }

    public static function create(int $indentLevel = 0, int $indentSize = 4, string $indentChar = ' '): self
    {
        return new self(
            new IndentStack($indentLevel, $indentSize, $indentChar),
        );
    }

    public static function forFile(bool $strictTypes = true): self
    {
        $code = self::create();
        $code->header .= '<?php';
        if ($strictTypes) {
            $code->header .= ' declare(strict_types=1);';
        }
        return $code;
    }

    public function indent(int $levels = 1): self
    {
        $this->indentStack->push($levels);
        return $this;
    }

    public function dedent(int $levels = 1): self
    {
        $this->indentStack->pop($levels);
        return $this;
    }

    public function raw(string $value): self
    {
        $this->body .= $value;
        return $this;
    }

    public function write(string $value): self
    {
        $this->body .= $this->indentStack . $value;
        return $this;
    }

    public function writeln(string ...$lines): self
    {
        $indent = (string)$this->indentStack;
        foreach ($lines as $line) {
            $this->body .= $indent . $line . "\n";
        }
        return $this;
    }

    /**
     * @param iterable $values
     * @param callable(mixed, mixed, self):mixed $cb
     * @return $this
     */
    public function each(iterable $values, callable $cb): self
    {
        foreach ($values as $k => $v) {
            $cb($v, $k, $this);
        }
        return $this;
    }

    /**
     * @param string $glue
     * @param iterable $values
     * @param callable(mixed, mixed, self):mixed $cb
     * @return $this
     */
    public function join(string $glue, iterable $values, callable $cb): self
    {
        $first = true;
        foreach ($values as $k => $v) {
            if (!$first) {
                $this->raw($glue);
            }
            $first = false;
            $cb($v, $k, $this);
        }
        return $this;
    }

    public function new(string $fqcn): self
    {
        $this->raw('new ')->className($fqcn);
        return $this;
    }

    public function use(string $name, string $alias = ''): self
    {
        $this->imports->addClass($name, $alias);
        return $this;
    }

    public function useFunction(string $name, string $alias = ''): self
    {
        $this->imports->addFunction($name, $alias);
        return $this;
    }

    public function useConst(string $name, string $alias = ''): self
    {
        $this->imports->addConstant($name, $alias);
        return $this;
    }

    public function className(string $class, bool $import = true): self
    {
        if ($import) {
            $this->use($class);
            $this->body .= Namespaces::remove($class);
        } else {
            $this->body .= $class;
        }
        return $this;
    }

    public function enum(\UnitEnum $value, bool $import = true): self
    {
        if ($import) {
            $this->use($value::class);
            $this->body .= Namespaces::remove($value::class);
        } else {
            $this->body .= $value::class;
        }
        $this->body .= '::' . $value->name;

        return $this;
    }

    public function repr(array|int|float|bool|string|null $value): self
    {
        return $this->raw(Repr::any($value));
    }

    public function string(string $value): self
    {
        $this->body .= Repr::string($value);
        return $this;
    }

    public function regexp(string $pattern): self
    {
        $this->body .= Repr::regexp($pattern);
        return $this;
    }

    public function int(int $value, int $base = 10): self
    {
        $this->body .= Repr::int($value, $base);
        return $this;
    }

    public function __toString(): string
    {
        if ($header = $this->header) {
            $header .= "\n\n";
        }
        if ($imports = $this->imports->all()) {
            foreach ($imports as $entry) {
                $header .= $entry . "\n";
            }
            $header .= "\n";
        }
        return $header . $this->body;
    }
}
