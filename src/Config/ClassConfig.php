<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\Node;
use PhpParser\NodeAbstract;

class ClassConfig extends AbstractConfig
{
    /**
     * @param string $name
     * @param DocCommentConfig $description
     * @param ClassPropertyConfig[] $properties
     * @param ClassMethodConfig[] $methods
     */
    public function __construct(
        protected readonly string $name,
        protected readonly DocCommentConfig $description,
        protected readonly array $properties,
        protected readonly array $methods,
    )
    {

    }

    public static function fromNode(
        NodeAbstract $node,
        ?string $inlineComment = null
    ): ?static
    {
        $properties = [];
        $methods = [];

        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Property) {
                $properties[] = ClassPropertyConfig::fromNode($stmt);
            } elseif ($stmt instanceof Node\Stmt\ClassMethod) {
                $methods[] = ClassMethodConfig::fromNode($stmt);
            }
        }

        return new (static::class)(
            name: $node->name->toString(),
            description: DocCommentConfig::fromNode($node),
            properties: $properties,
            methods: $methods,
        );
    }

    public function toConfig(): array
    {
        $config = [
            'name' => $this->name,
            'type' => 'class',
        ];

        if ($this->description) {
            $config['description'] = $this->description->toConfig();
        }

        if (!empty($this->properties)) {
            $config['properties'] = ClassPropertyConfig::collectionToConfig($this->properties);
        }

        if (!empty($this->methods)) {
            $config['methods'] = ClassMethodConfig::collectionToConfig($this->methods);
        }

        return $config;
    }

    public function toCode(): string
    {
        $output = '';

        if ($this->description) {
            $output .= $this->description->toCode();
        }

        $output .= "class {$this->name}\n{\n";

        foreach ($this->properties as $property) {
            $output .= $property->toCode();
        }

        foreach ($this->methods as $method) {
            $output .= $method->toCode();
        }

        $output .= "}\n";

        return $output;
    }
}