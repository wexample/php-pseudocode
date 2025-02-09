<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\Node;
use PhpParser\NodeAbstract;
use Wexample\Helpers\Class\Traits\HasSnakeShortClassNameClassTrait;

abstract class AbstractConfig
{
    use HasSnakeShortClassNameClassTrait;

    public function __construct(
        protected ?GeneratorConfig $generator = null
    )
    {

    }

    abstract public static function fromNode(
        NodeAbstract $node,
        ?string $inlineComment = null
    ): ?static;

    /**
     * Check if this config can load the given config value.
     */
    public static function canLoad(
        array $data
    ): bool
    {
        return false;
    }

    /**
     * Check if this config can parse the given node.
     */
    public static function canParse(Node $node): bool
    {
        return false;
    }

    public static function fromConfig(
        mixed $data,
        ?GeneratorConfig $globalGeneratorConfig = null
    ): ?static
    {
        $data = static::unpackData($data);

        if (isset($data['generator']) || $globalGeneratorConfig) {
            $data['generator'] = GeneratorConfig::fromConfig($data['generator'] ?? null, $globalGeneratorConfig);
        }

        return new static(...$data);
    }

    protected static function unpackData(mixed $data): array
    {
        if (!is_array($data)) {
            throw new \Error('Bad data format passed to ' . static::class . ', you should implement unpackData() to help resolving data conversion to unpackable array.');
        }

        return $data;
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
        if ($expr instanceof Node\Expr\Array_) {
            return [];
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
            return self::getTypeName($type->type);
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
            return "'" . addslashes($value) . "'";
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

    /**
     * @param array $items
     * @param GeneratorConfig|null $globalGeneratorConfig
     * @return AbstractConfig[]
     */
    public static function collectionFromConfig(
        array $items,
        ?GeneratorConfig $globalGeneratorConfig = null
    ): array
    {
        $config = [];
        foreach ($items as $item) {
            $config[] = static::fromConfig(
                $item,
                $globalGeneratorConfig
            );
        }
        return $config;
    }

    public function toConfig(?AbstractConfig $parentConfig = null): mixed
    {
        return null;
    }

    protected function getIndentation(int $level): string
    {
        return str_repeat('    ', $level);
    }

    public function toCode(?AbstractConfig $parentConfig = null, int $indentationLevel = 0): ?string
    {
        return null;
    }
}