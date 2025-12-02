<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\Node;

class GeneratorConfig extends AbstractConfig
{
    public function __construct(
        public readonly string $constantDeclaration,
        ?GeneratorConfig $generator = null,
    ) {
        parent::__construct(
            generator: $generator,
        );
    }

    public static function fromConfig(
        mixed $data,
        ?GeneratorConfig $globalGeneratorConfig = null
    ): ?static {
        if (! $data or ! isset($data['php'])) {
            return $globalGeneratorConfig;
        }

        return parent::fromConfig($data['php'], $globalGeneratorConfig);
    }

    public static function fromNode(
        Node $node,
        ?string $inlineComment = null
    ): ?static {
        return null;
    }

    public function toConfig(?AbstractConfig $parentConfig = null): array
    {
        return [];
    }

    public function toCode(?AbstractConfig $parentConfig = null, int $indentationLevel = 0): null
    {
        return null;
    }
}
