<?php

namespace Wexample\Pseudocode\Item;

interface ConvertibleInterface
{
    /**
     * Convert the item to PHP code
     */
    public function toPhp(): string;

    /**
     * Convert the item to YAML array structure
     */
    public function toYaml(): array;

    /**
     * Create an item from PHP code
     */
    public static function fromPhp(string $code): self;

    /**
     * Create an item from YAML array structure
     */
    public static function fromYaml(array $data): self;
}
