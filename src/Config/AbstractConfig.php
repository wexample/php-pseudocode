<?php

namespace Wexample\Pseudocode\Item;

use PhpParser\Node;
use PhpParser\NodeAbstract;

abstract class AbstractConfig
{
    abstract public static function fromNode(
        NodeAbstract $node,
        ?string $inlineComment = null
    ): ?static;

    protected static function parseValue(Node\Expr $expr): mixed
    {
        if ($expr instanceof Node\Scalar\String_) {
            return $expr->value;
        }
        if ($expr instanceof Node\Scalar\LNumber) {
            return $expr->value;
        }
        if ($expr instanceof Node\Scalar\DNumber) {
            return $expr->value;
        }
        if ($expr instanceof Node\Expr\ConstFetch) {
            return $expr->name->toString() === 'true';
        }
        return null;
    }

    protected static function getTypeName($type): string
    {
        if ($type instanceof Node\UnionType) {
            return implode('|', array_map(fn(
                $t
            ) => self::getTypeName($t), $type->types));
        }
        if ($type instanceof Node\NullableType) {
            return self::getTypeName($type->type) . '|null';
        }
        if ($type instanceof Node\Name) {
            return $type->toString();
        }
        if ($type instanceof Node\Identifier) {
            return $type->toString();
        }
        return 'mixed';
    }

    /**
     * @param AbstractConfig[] $items
     * @return array
     */
    public static function collectionToConfig(array $items): array
    {
        $config = [];
        foreach ($items as $item) {
            $config[] = $item->toConfig();
        }
        return $config;
    }

    public function toConfig(): mixed
    {
        return null;
    }
}