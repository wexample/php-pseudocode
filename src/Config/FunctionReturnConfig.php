<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\NodeAbstract;
use Wexample\Pseudocode\Config\AbstractConfig;

class FunctionReturnConfig extends AbstractConfig
{
    public function __construct(
        protected readonly string $type,
    )
    {

    }

    public function toConfig(): string
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

    public function toCode(): string
    {
        return " * @return " . $this->type . "\n";
    }
}