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

    /**
     * Creates a new CodeBuilder with the provided indentation settings.
     *
     * @return static
     */
    public static function create(int $indentLevel = 0, int $indentSize = 4, string $indentChar = ' '): self
    {
        return new self(
            new IndentStack($indentLevel, $indentSize, $indentChar),
        );
    }

    /**
     * Creates a new CodeBuilder with the correct header,
     * so that the generated code can be included.
     *
     * @return static
     */
    public static function forFile(bool $strictTypes = true): self
    {
        $code = self::create();
        $code->header .= '<?php';
        if ($strictTypes) {
            $code->header .= ' declare(strict_types=1);';
        }
        return $code;
    }

    /**
     * Increases the indent level by the provided amount.
     *
     * @return $this
     */
    public function indent(int $levels = 1): self
    {
        $this->indentStack->push($levels);
        return $this;
    }

    /**
     * Decreases the indent level by the provided amount.
     *
     * @return $this
     */
    public function dedent(int $levels = 1): self
    {
        $this->indentStack->pop($levels);
        return $this;
    }

    /**
     * Adds verbatim code, without indentation.
     * @return $this
     */
    public function raw(string $value): self
    {
        $this->body .= $value;
        return $this;
    }

    /**
     * Adds verbatim code, respecting the current indentation level.
     *
     * @return $this
     */
    public function write(string $value): self
    {
        $this->body .= $this->indentStack . $value;
        return $this;
    }

    /**
     * Adds verbatim code, respecting the current indentation level,
     * with a newline character after each provided argument.
     *
     * @return $this
     */
    public function writeln(string ...$lines): self
    {
        $indent = (string)$this->indentStack;
        foreach ($lines as $line) {
            $this->body .= $indent . $line . "\n";
        }
        return $this;
    }

    /**
     * Applies the provided callable to each key-value pair of an iterable.
     * The callable receives the current value and key of the iterable,
     * and the CodeBuilder instance as a third argument.
     * The return value of the callable is not used.
     *
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
     * For each key-value pair of the iterable,
     * writes the return value of the callable, and joins the result
     * using the provided glue string.
     * The callable receives the current value and key of the iterable,
     * and the CodeBuilder instance as a third argument.
     *
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

    /**
     * Imports the given class name using the provided alias.
     *
     * @return $this
     */
    public function use(string $name, string $alias = ''): self
    {
        $this->imports->addClass($name, $alias);
        return $this;
    }

    /**
     * Imports the given function name using the provided alias.
     *
     * @return $this
     */
    public function useFunction(string $name, string $alias = ''): self
    {
        $this->imports->addFunction($name, $alias);
        return $this;
    }

    /**
     * Imports the given constant name using the provided alias.
     *
     * @return $this
     */
    public function useConst(string $name, string $alias = ''): self
    {
        $this->imports->addConstant($name, $alias);
        return $this;
    }

    /**
     * Convenience method that writes `new $class` and imports the given class.
     *
     * @param string $fqcn Fully qualified class name.
     * @return $this
     */
    public function new(string $fqcn): self
    {
        $this->raw('new ')->className($fqcn);
        return $this;
    }

    /**
     * Writes the provided class name, and optionally imports it.
     *
     * @return $this
     */
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

    /**
     * Writes the provided enum case as PHP code, optionally importing the enum class.
     *
     * @return $this
     */
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

    /**
     * Writes the provided value as PHP code.
     * Supports scalars, nulls and arrays thereof, recursively.
     *
     * @return $this
     */
    public function repr(array|int|float|bool|string|null $value): self
    {
        return $this->raw(Repr::any($value));
    }

    /**
     * Writes a string value as PHP code, properly escaping quotes and special characters.
     *
     * @return $this
     */
    public function string(string $value): self
    {
        $this->body .= Repr::string($value);
        return $this;
    }

    /**
     * Writes a PCRE pattern string value as PHP code, properly escaping quotes and backslashes.
     *
     * @return $this
     */
    public function regexp(string $pattern): self
    {
        $this->body .= Repr::regexp($pattern);
        return $this;
    }

    /**
     * Writes an integer value as PHP code, using the provided base.
     *
     * @return $this
     */
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
