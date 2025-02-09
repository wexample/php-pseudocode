<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\NodeAbstract;

class FunctionReturnConfig extends AbstractConfig
{
    public function __construct(
        protected readonly string $type,
        array $generator = [],
    )
    {
        parent::__construct(
            generator: $generator
        );
    }

    protected static function unpackData(mixed $data): array
    {
        if (!is_array($data)) {
            return ['type' => (string) $data];
        }

        return parent::unpackData($data);
    }

    public function toConfig(?AbstractConfig $parentConfig = null): string
    {
        return $this->type;
    }

    public static function fromNode(
        NodeAbstract $node,
        ?string $inlineComment = null
    ): ?static
    {
        return $node->returnType ? new (static::class)(type: self::getTypeName($node->returnType)) : null;
    }

    public function toCode(?AbstractConfig $parentConfig = null): string
    {
        return ($this->type ?? 'void');
    }
}