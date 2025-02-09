<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\NodeAbstract;

class FunctionReturnConfig extends AbstractConfig
{
    public function __construct(
        protected readonly string $type,
        protected readonly ?DocCommentReturnConfig $description = null,
        ?GeneratorConfig $generator = null,
    )
    {
        parent::__construct(
            generator: $generator
        );
    }

    protected static function unpackData(mixed $data): array
    {
        if (!is_array($data)) {
            return ['type' => (string) $data];
        }

        return parent::unpackData($data);
    }

    public function toConfig(?AbstractConfig $parentConfig = null): array|string
    {
        if (!$this->description) {
            return $this->type;
        }

        return [
            'type' => $this->type,
            'description' => $this->description->description,
        ];
    }

    public static function fromConfig(
        mixed $data,
        ?GeneratorConfig $globalGeneratorConfig = null
    ): ?static
    {
        $data = static::unpackData($data);

        if (isset($data['description'])) {
            $data['description'] = DocCommentReturnConfig::fromConfig($data['description'], $globalGeneratorConfig);
        }

        return new static(...$data);
    }

    public static function fromNode(
        NodeAbstract $node,
        ?string $inlineComment = null,
        ?DocCommentReturnConfig $description = null
    ): ?static
    {
        if (!$node->returnType) {
            return null;
        }

        return new static(
            type: self::getTypeName($node->returnType),
            description: $description
        );
    }

    public function toCode(
        ?AbstractConfig $parentConfig = null,
        int $indentationLevel = 0
    ): string
    {
        return ($this->type ?? 'void');
    }
}