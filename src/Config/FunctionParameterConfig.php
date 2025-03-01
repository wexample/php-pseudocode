<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\NodeAbstract;
use Wexample\Pseudocode\Enum\ConfigEnum;
use Wexample\Pseudocode\Helper\PhpNodeHelper;

class FunctionParameterConfig extends AbstractConfig
{
    public function __construct(
        protected readonly string $type,
        protected readonly string $name,
        protected readonly mixed $default = ConfigEnum::NOT_PROVIDED,
        protected readonly bool $optional = false,
        protected readonly ?DocCommentConfig $description = null,
        ?GeneratorConfig $generator = null,
    )
    {
        parent::__construct(
            generator: $generator
        );
    }

    public static function fromConfig(
        mixed $data,
        ?GeneratorConfig $globalGeneratorConfig = null
    ): ?static
    {
        if (isset($data['description'])) {
            $data['description'] = DocCommentConfig::fromConfig($data['description'], $globalGeneratorConfig);
        }

        return parent::fromConfig($data);
    }

    public static function fromNode(
        NodeAbstract $node,
        null|string|DocCommentConfig $inlineComment = null
    ): ?static
    {
        if (!$node instanceof \PhpParser\Node\Param) {
            return null;
        }

        return new static(
            type: $node->type ? self::getTypeName($node->type) : 'mixed',
            name: $node->var->name,
            default: $node->default !== null
                ? self::parseValue($node->default)
                : ConfigEnum::NOT_PROVIDED,
            optional: PhpNodeHelper::isOptional($node),
            description: $inlineComment
        );
    }

    public function toConfig(?AbstractConfig $parentConfig = null): array
    {
        $config = [
            'type' => $this->type,
            'name' => $this->name,
        ];

        if ($this->optional) {
            $config['optional'] = $this->optional;
        }

        if ($this->default !== ConfigEnum::NOT_PROVIDED) {
            $config['default'] = $this->default;
        }

        if ($this->description) {
            $config['description'] = $this->description->toConfig();
        }

        return $config;
    }

    public function toCode(?AbstractConfig $parentConfig = null, int $indentationLevel = 0): string
    {
        $defaultCode = '';
        if ($this->default !== ConfigEnum::NOT_PROVIDED) {
            $defaultCode = ' = ' . $this->convertDefaultValueToPhpCode($this->default);
        }

        return ($this->optional ? '?' : '')
            . $this->type
            . ' $' . $this->name
            . $defaultCode;
    }

    private function convertDefaultValueToPhpCode($value): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_array($value)) {
            return $this->convertArrayToPhpCode($value);
        }

        return var_export($value, true);
    }

    private function convertArrayToPhpCode(array $array): string
    {
        $items = [];
        foreach ($array as $key => $value) {
            $keyCode = is_int($key) ? '' : var_export($key, true) . ' => ';
            $items[] = $keyCode . $this->convertDefaultValueToPhpCode($value);
        }
        return '[' . implode(', ', $items) . ']';
    }
}