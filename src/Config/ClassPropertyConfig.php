<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\NodeAbstract;
use Wexample\Pseudocode\Enum\ConfigEnum;
use Wexample\Pseudocode\Helper\DocCommentParserHelper;

class ClassPropertyConfig extends AbstractConfig
{
    public function __construct(
        protected readonly string $name,
        protected readonly string $type,
        protected readonly ?DocCommentConfig $description = null,
        protected readonly mixed $default = ConfigEnum::NOT_PROVIDED,
        ?GeneratorConfig $generator = null,
    )
    {
        parent::__construct(
            generator: $generator,
        );
    }

    public static function fromNode(
        NodeAbstract $node,
        ?string $inlineComment = null
    ): ?static
    {
        return new static(
            name: $node->props[0]->name->name,
            type: self::getTypeName($node->type),
            description: DocCommentParserHelper::extractDescriptionFromNode($node),
            default: $node->props[0]->default !== null
                ? self::parseValue($node->props[0]->default)
                : ConfigEnum::NOT_PROVIDED,
        );
    }

    public static function fromConfig(
        mixed $data,
        ?GeneratorConfig $globalGeneratorConfig = null
    ): ?static
    {
        if (isset($data['description'])) {
            $data['description'] = DocCommentConfig::fromConfig(
                $data['description'],
                $globalGeneratorConfig
            );
        }

        return parent::fromConfig($data);
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

        if ($this->default !== ConfigEnum::NOT_PROVIDED) {
            $config['default'] = $this->default;
        }

        return $config;
    }

    public function toCode(
        ?AbstractConfig $parentConfig = null,
        int $indentationLevel = 0
    ): string
    {
        $output = '';

        if ($this->description) {
            $output .= $this->description->toCode(
                parentConfig: $this,
                indentationLevel: $indentationLevel,
                prefix: '@var ' . $this->type. ' ',
                format: 'inlineBlock'
            );
        }

        $default = $this->default !== ConfigEnum::NOT_PROVIDED
            ? " = " . $this->formatValue($this->default)
            : "";

        $output .= $this->getIndentation($indentationLevel) . "private {$this->type} \${$this->name}{$default};\n";

        return $output;
    }
}