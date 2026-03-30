<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\NodeAbstract;
use Wexample\Pseudocode\Parser\ParserContext;

abstract class AbstractDocCommentParameterConfig extends AbstractConfig
{
    public function __construct(
        protected readonly string $type,
        public readonly ?string $description = null,
        ?GeneratorConfig $generator = null,
    ) {
        parent::__construct(
            generator: $generator
        );
    }

    public function toConfig(?AbstractConfig $parentConfig = null): mixed
    {
        return [
            'type' => $this->type,
            'description' => $this->description,
        ];
    }

    public static function fromNode(
        NodeAbstract $node,
        mixed $inlineComment = null,
        ?ParserContext $context = null
    ): ?static {
        return null;
    }
}
