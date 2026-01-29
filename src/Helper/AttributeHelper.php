<?php

namespace Wexample\Pseudocode\Helper;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Identifier;
use Wexample\Pseudocode\Parser\ClassIndex;

class AttributeHelper
{
    public static function hasAttribute(Node $node, string $attributeFqcn, ?string $shortName = null): bool
    {
        return self::findAttribute($node, $attributeFqcn, $shortName) instanceof Attribute;
    }

    public static function findAttribute(Node $node, string $attributeFqcn, ?string $shortName = null): ?Attribute
    {
        if (! property_exists($node, 'attrGroups')) {
            return null;
        }

        $shortName = $shortName ?? self::shortName($attributeFqcn);

        foreach ($node->attrGroups as $group) {
            foreach ($group->attrs as $attribute) {
                $resolved = $attribute->name->getAttribute('resolvedName');
                if ($resolved instanceof Node\Name && $resolved->toString() === $attributeFqcn) {
                    return $attribute;
                }

                $name = $attribute->name->toString();
                if ($name === $attributeFqcn || $name === $shortName || self::endsWith($name, '\\' . $shortName)) {
                    return $attribute;
                }
            }
        }

        return null;
    }

    public static function findAttributeInHierarchy(
        Node\Stmt\Class_ $node,
        ClassIndex $classIndex,
        string $attributeFqcn,
        string $propagateOption = 'propagate',
        int $propagatePosition = 1
    ): ?Attribute {
        $attribute = self::findAttribute($node, $attributeFqcn);
        if ($attribute) {
            return $attribute;
        }

        $parentNode = self::getParentClassNode($node, $classIndex);
        if (! $parentNode) {
            return null;
        }

        $parentAttribute = self::findAttribute($parentNode, $attributeFqcn);
        if ($parentAttribute) {
            $propagate = self::getAttributeBoolOption(
                $parentAttribute,
                $propagateOption,
                $propagatePosition,
                false
            );

            if (! $propagate) {
                return null;
            }

            return $parentAttribute;
        }

        return self::findAttributeInHierarchy(
            $parentNode,
            $classIndex,
            $attributeFqcn,
            $propagateOption,
            $propagatePosition
        );
    }

    public static function getAttributeBoolOption(
        Attribute $attribute,
        string $name,
        int $position,
        bool $default
    ): bool {
        foreach ($attribute->args as $arg) {
            if ($arg->name instanceof Identifier && $arg->name->toString() === $name) {
                return self::parseBool($arg);
            }
        }

        if (isset($attribute->args[$position])) {
            return self::parseBool($attribute->args[$position]);
        }

        return $default;
    }

    private static function shortName(string $fqcn): string
    {
        $parts = explode('\\', $fqcn);

        return end($parts) ?: $fqcn;
    }

    private static function endsWith(string $value, string $suffix): bool
    {
        if ($suffix === '') {
            return true;
        }

        return substr($value, -strlen($suffix)) === $suffix;
    }

    private static function getParentClassNode(
        Node\Stmt\Class_ $node,
        ClassIndex $classIndex
    ): ?Node\Stmt\Class_ {
        if (! $node->extends instanceof Node\Name) {
            return null;
        }

        $resolved = $node->extends->getAttribute('resolvedName');
        $parentName = $resolved instanceof Node\Name ? $resolved : $node->extends;

        return $classIndex->getClass($parentName->toString());
    }

    private static function parseBool(Arg $arg): bool
    {
        $value = $arg->value;
        if ($value instanceof ConstFetch) {
            $const = strtolower($value->name->toString());
            if ($const === 'true') {
                return true;
            }
            if ($const === 'false') {
                return false;
            }
        }

        return (bool) ($value->value ?? $value);
    }
}
