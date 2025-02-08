<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\NodeAbstract;

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
        $description = null;
        if ($node->getDocComment()) {
            $docComment = $node->getDocComment()->getText();
            // Remove /** and */ markers
            $docComment = preg_replace('/^\/\*\*|\*\/$/', '', $docComment);
            // Remove @var type if present and get the actual description
            if (preg_match('/@var\s+\S+\s+(.+)/', $docComment, $matches)) {
                $description = new DocCommentConfig(trim($matches[1]));
            }
        }

        return new (static::class)(
            name: $node->props[0]->name->name,
            type: self::getTypeName($node->type),
            description: $description,
            default: $node->props[0]->default ? self::parseValue($node->props[0]->default) : null,
        );
    }

    public function toConfig(?AbstractConfig $parentConfig = null): array
    {
        $config = [
            'name' => $this->name,
            'type' => $this->type,
        ];

        if ($this->description) {
            $config['description'] = $this->description->toConfig();
        }

        if ($this->default !== null) {
            $config['default'] = $this->default;
        }

        return $config;
    }

    public function toCode(?AbstractConfig $parentConfig): string
    {
        $output = '';

        if ($this->description) {
            $output .= $this->description->toCode($this);
        }

        $default = isset($this->default)
            ? " = " . $this->formatValue($this->default)
            : "";

        $output .= "    private {$this->type} \${$this->name}{$default};\n\n";

        return $output;
    }
}