<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\NodeAbstract;

class FunctionParameterConfig extends AbstractConfig
{
    public function __construct(
        protected readonly string $type,
        protected readonly string $name,
        protected readonly ?DocCommentConfig $description = null
    )
    {

    }

    public static function fromConfig(mixed $data): ?static
    {
        if (isset($data['description'])) {
            $data['description'] = DocCommentConfig::fromConfig($data['description']);
        }

        return parent::fromConfig($data);
    }

    public static function fromNode(
        NodeAbstract $node,
        ?string $inlineComment = null
    ): ?static
    {
        return null;
    }

    public function toConfig(?AbstractConfig $parentConfig = null): array
    {
        $config = [
            'type' => $this->type,
            'name' => $this->name,
        ];

        if ($this->description) {
            $config['description'] = $this->description->toConfig();
        }

        return $config;
    }

    public function toCode(?AbstractConfig $parentConfig = null): string
    {
        return $this->type . ' $' . $this->name;
    }
}