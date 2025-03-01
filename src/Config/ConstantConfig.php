<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Const_;
use PhpParser\NodeAbstract;

class ConstantConfig extends AbstractConfig
{
    public function __construct(
        private readonly string $name,
        private readonly mixed $value,
        private readonly DocCommentConfig $description,
        protected readonly string $type = 'constant',
        ?GeneratorConfig $generator = null,
    )
    {
        parent::__construct(
            generator: $generator,
        );
    }

    public static function canLoad(array $data): bool
    {
        return $data['type'] === 'constant';
    }

    public static function canParse(Node $node): bool
    {
        return ($node instanceof Node\Expr\FuncCall && $node->name->toString() === 'define')
            || ($node instanceof Const_);
    }

    public static function fromNode(
        NodeAbstract $node,
        ?string $inlineComment = null
    ): ?static
    {
        if ($node instanceof FuncCall) {
            // Handle define() calls
            $name = $node->args[0]->value->value;
            $value = static::parseValue($node->args[1]->value);
        } elseif ($node instanceof Const_) {
            // Handle const keyword
            $const = $node->consts[0];
            $name = $const->name->toString();
            $value = static::parseValue($const->value);
        } else {
            return null;
        }

        if ($inlineComment) {
            $description = new DocCommentConfig(description: $inlineComment);
        }

        return new (static::class)(
            name: $name,
            value: $value,
            description: $description
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

        return parent::fromConfig($data, $globalGeneratorConfig);
    }

    public function toConfig(?AbstractConfig $parentConfig = null): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'value' => $this->value,
            'description' => $this->description->toConfig()
        ];
    }

    public function toCode(
        ?AbstractConfig $parentConfig = null,
        int $indentationLevel = 0
    ): string
    {
        $indentation = $this->getIndentation($indentationLevel);
        $descriptionCode = $this->description?->toCode(format: 'inline');
        $descriptionCode = $descriptionCode !== '' ? ' ' . $descriptionCode : '';

        if ($this->generator && $this->generator->constantDeclaration === 'define') {
            return $indentation
                . "define('"
                . $this->name
                . "', "
                . $this->formatValue($this->value)
                . ");"
                . $descriptionCode;
        } else {
            return $indentation
                . "const "
                . $this->name
                . " = "
                . $this->formatValue($this->value)
                . ";"
                . $descriptionCode;
        }
    }
}