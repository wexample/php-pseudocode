<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\NodeAbstract;
use Wexample\Pseudocode\Item\AbstractConfig;

class ClassPropertyConfig extends AbstractConfig
{
    public function __construct(
        protected readonly string $name,
        protected readonly string $type,
        protected readonly DocCommentConfig $description,
        protected readonly mixed $default,
    )
    {

    }

    public static function fromNode(
        NodeAbstract $node,
        ?string $inlineComment = null
    ): ?static
    {
        return new (static::class)(
            name: $node->name->toString(),
            type: self::getTypeName($node->type),
            description: DocCommentConfig::fromNode($node),
            default: $node->props[0]->default ? self::parseValue($node->props[0]->default) : null,
        );
    }

    public function toConfig(): array
    {
        $config = [
            'name' => $this->name,
            'type' => $this->type,
        ];

        if ($this->description) {
            $config['description'] = $this->description->toConfig();
        }

        if ($this->default) {
            $config['default'] = $this->default;
        }

        return $config;
    }
}