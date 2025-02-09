<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\Node;
use PhpParser\NodeAbstract;
use Wexample\Helpers\Class\Traits\HasSnakeShortClassNameClassTrait;

abstract class AbstractConfig
{
    use HasSnakeShortClassNameClassTrait;

    abstract public static function fromNode(
        NodeAbstract $node,
        ?string $inlineComment = null
    ): ?static;

    /**
     * Check if this config can parse the given node.
     */
    public static function canParse(Node $node): bool
    {
        return false;
    }

    public static function fromData(array $data): ?static
    {
        return new static(...$data);
    }

    protected static function getClassNameSuffix(): ?string
    {
        return 'Config';
    }

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
            $const = strtolower($expr->name->toString());

            if ($const === 'true') {
                return true;
            }
            if ($const === 'false') {
                return false;
            }
            if ($const === 'null') {
                return null;
            }
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
        return (string) $value;
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

    public function toConfig(?AbstractConfig $parentConfig = null): mixed
    {
        return null;
    }

    public function toCode(?AbstractConfig $parentConfig): ?string
    {
        return null;
    }
}