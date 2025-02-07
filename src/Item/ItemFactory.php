<?php

namespace Wexample\Pseudocode\Item;

class ItemFactory
{
    public function createFromArray(array $data): AbstractItem
    {
        if (!isset($data['type'])) {
            throw new \InvalidArgumentException('Type is required');
        }

        return match($data['type']) {
            'constant' => new ConstantItem($data),
            'function' => new FunctionItem($data),
            'class' => new ClassItem($data),
            default => throw new \InvalidArgumentException("Unknown item type: {$data['type']}")
        };
    }
}
