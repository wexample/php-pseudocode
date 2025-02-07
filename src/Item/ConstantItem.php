<?php

namespace Wexample\Pseudocode\Item;

use PhpParser\NodeAbstract;

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

    public static function fromNode(NodeAbstract $node): array
    {
        $data = [
            'type' => 'constant',
            'name' => $node->args[0]->value->value,
            'value' => static::parseValue($node->args[1]->value),
        ];

        // Description will be set by the parser if an inline comment is found
        return $data;
    }
}
