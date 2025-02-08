<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\NodeAbstract;

abstract class AbstractDocCommentParameterConfig extends AbstractConfig
{
    public function __construct(
        protected readonly string $type,
        protected readonly string $description
    )
    {

    }

    public function toConfig(?AbstractConfig $parentConfig = null): mixed
    {
        return [
            'type' => $this->type,
            'description' => $this->description
        ];
    }

    public static function fromNode(
        NodeAbstract $node,
        ?string $inlineComment = null
    ): ?static
    {
        return null;
    }
}