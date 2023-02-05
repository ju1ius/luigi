<?php declare(strict_types=1);

namespace ju1ius\Luigi\Internal;

final class IndentStack implements \Stringable
{
    public function __construct(
        private int $level = 0,
        private readonly int $size = 4,
        private readonly string $char = ' '
    ) {
    }

    public function push(int $levels = 1): self
    {
        $this->level = max(0, $this->level + $levels);
        return $this;
    }

    public function pop(int $levels = 1): self
    {
        $this->level = max(0, $this->level - $levels);
        return $this;
    }

    public function __toString(): string
    {
        return str_repeat($this->char, $this->size * $this->level);
    }
}
