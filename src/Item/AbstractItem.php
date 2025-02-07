<?php

namespace Wexample\Pseudocode\Item;

abstract class AbstractItem
{
    protected string $name;
    protected ?string $description;

    public function __construct(array $data)
    {
        $this->name = $data['name'] ?? throw new \InvalidArgumentException('Name is required');
        $this->description = $data['description'] ?? null;
    }

    abstract public function generateCode(): string;

    protected function formatDocBlock(?string $description = null): string
    {
        if (!$description) {
            return '';
        }

        return "/**\n * {$description}\n */\n";
    }

    protected function formatValue(mixed $value): string
    {
        if (is_string($value)) {
            return '"' . addslashes($value) . '"';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_null($value)) {
            return 'null';
        }
        return (string)$value;
    }
}
