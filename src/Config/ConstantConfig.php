<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeAbstract;
use Wexample\Pseudocode\Item\AbstractConfig;

class ConstantConfig extends AbstractConfig
{
    public function __construct(
        private string $name,
        private mixed $value,
        private DocCommentConfig $description
    )
    {

    }

    public static function fromNode(
        NodeAbstract $node,
        ?string $inlineComment = null
    ): ?static
    {
        if ($node instanceof FuncCall) {
            // Handle define() calls
            $name = $node->args[0]->value->value;
            $value = static::parseValue($node->args[1]->value);
            $description = new DocCommentConfig(description: $inlineComment);
        } elseif ($node instanceof \PhpParser\Node\Stmt\Const_) {
            // Handle const keyword
            $const = $node->consts[0];
            $name = $const->name->toString();
            $value = static::parseValue($const->value);
            $description = DocCommentConfig::fromNode($node);
        }

        return new (static::class)(
            name: $name,
            value: $value,
            description: $description
        );
    }

    public function toConfig(): array
    {
        return [
            'type' => 'constant',
            'name' => $this->name,
            'value' => $this->value,
            'description' => $this->description->toConfig()
        ];
    }
}