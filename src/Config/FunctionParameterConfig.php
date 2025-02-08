<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\NodeAbstract;
use Wexample\Pseudocode\Item\AbstractConfig;

class FunctionParameterConfig extends AbstractConfig
{
    public function __construct(
        protected readonly string $type,
        protected readonly string $name
    )
    {

    }

    public static function fromNode(
        NodeAbstract $node,
        ?string $inlineComment = null
    ): ?static
    {
        return null;
    }

    public function toConfig(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
        ];
    }
}