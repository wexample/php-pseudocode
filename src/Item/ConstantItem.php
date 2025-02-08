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

    public static function fromNode(NodeAbstract $node, ?string $inlineComment = null): array
    {
        if ($node instanceof \PhpParser\Node\Expr\FuncCall) {
            // Handle define() calls
            $data = [
                'type' => 'constant',
                'name' => $node->args[0]->value->value,
                'value' => static::parseValue($node->args[1]->value),
            ];

            if ($inlineComment !== null) {
                $data['description'] = $inlineComment;
            }
        } elseif ($node instanceof \PhpParser\Node\Stmt\Const_) {
            // Handle const keyword
            $const = $node->consts[0];
            $data = [
                'type' => 'constant',
                'name' => $const->name->toString(),
                'value' => static::parseValue($const->value),
                'description' => self::getDocComment($node)
            ];
        } else {
            throw new \InvalidArgumentException('Unsupported node type for constant');
        }

        return $data;
    }
}
