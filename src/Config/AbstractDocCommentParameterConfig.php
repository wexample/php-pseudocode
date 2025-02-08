<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\NodeAbstract;
use Wexample\Pseudocode\Item\AbstractConfig;

abstract class AbstractDocCommentParameterConfig extends AbstractConfig
{
    public function __construct(
        protected readonly string $type,
        protected readonly string $description
    )
    {

    }

    public function toConfig(): mixed
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