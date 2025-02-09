<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeAbstract;

class ConstantConfig extends AbstractConfig
{
    public function __construct(
        private readonly string $name,
        private readonly mixed $value,
        private readonly DocCommentConfig $description,
        protected readonly string $type = 'constant',
        array $generator = [],
    )
    {
        parent::__construct(
            generator: $generator,
        );
    }

    public static function canLoad(array $data): bool
    {
        return $data['type'] === 'constant';
    }

    public static function canParse(Node $node): bool
    {
        return ($node instanceof Node\Expr\FuncCall && $node->name->toString() === 'define')
            || ($node instanceof Node\Stmt\Const_);
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

    public static function fromConfig(mixed $data): ?static
    {
        if (isset($data['description'])) {
            $data['description'] = DocCommentConfig::fromConfig($data['description']);
        }

        return parent::fromConfig($data);
    }

    public function toConfig(?AbstractConfig $parentConfig = null): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'value' => $this->value,
            'description' => $this->description->toConfig()
        ];
    }

    public function toCode(?AbstractConfig $parentConfig = null): string
    {
        return sprintf(
            "define('%s', %s); // %s\n",
            $this->name,
            $this->formatValue($this->value),
            $this->description?->toCode()
        );
    }
}