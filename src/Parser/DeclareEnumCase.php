<?php declare(strict_types=1);

namespace Paneon\PhpToTypeScript\Parser;

class DeclareEnumCase
{
    protected int $indentSize = 2;
    protected string $indent = '  ';

    public function __construct(
        protected string $name,
        protected string|int|null $value = null
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string|int|null
    {
        return $this->value;
    }

    public function toEnumCaseString(): string
    {
        if ($this->value === null) {
            return "{$this->indent}{$this->name},";
        }

        if (is_string($this->value)) {
            return "{$this->indent}{$this->name} = '{$this->value}',";
        }

        return "{$this->indent}{$this->name} = {$this->value},";
    }

    public function toUnionPart(): string
    {
        if ($this->value === null) {
            return "'{$this->name}'";
        }

        if (is_string($this->value)) {
            return "'{$this->value}'";
        }

        return (string) $this->value;
    }

    public function setIndentSize(int $indentSize): DeclareEnumCase
    {
        $this->indentSize = $indentSize;
        $this->indent = str_pad('', $indentSize);

        return $this;
    }
}
