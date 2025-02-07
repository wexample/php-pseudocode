<?php

namespace Wexample\Pseudocode\Item;

class ConstantItem extends AbstractItem
{
    private mixed $value;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->value = $data['value'] ?? throw new \InvalidArgumentException('Value is required for constant');
    }

    public function generateCode(): string
    {
        return sprintf(
            "define('%s', %s); // %s\n",
            $this->name,
            $this->formatValue($this->value),
            $this->description ?? ''
        );
    }
}
