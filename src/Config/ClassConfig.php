<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\Node;

class ClassConfig extends AbstractConfig
{
    /**
     * @param string $name
     * @param DocCommentConfig|null $description
     * @param ClassPropertyConfig[] $properties
     * @param ClassMethodConfig[] $methods
     * @param string $type
     */
    public function __construct(
        protected readonly string $name,
        protected readonly array $properties,
        protected readonly array $methods,
        protected readonly ?DocCommentConfig $description = null,
        protected readonly string $type = 'class',
    )
    {

    }

    public static function canParse(Node $node): bool
    {
        return $node instanceof Node\Stmt\Class_;
    }

    public static function canLoad(array $data): bool
    {
        return $data['type'] === 'class';
    }

    public static function fromConfig(mixed $data): ?static
    {
        if (isset($data['description'])) {
            $data['description'] = DocCommentConfig::fromConfig($data['description']);
        }

        $data['properties'] = ClassPropertyConfig::collectionFromConfig($data['properties'] ?? []);
        $data['methods'] = ClassMethodConfig::collectionFromConfig($data['methods'] ?? []);

        return parent::fromConfig($data);
    }

    public static function fromNode(
        Node $node,
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

    public function toConfig(?AbstractConfig $parentConfig = null): array
    {
        $config = [
            'name' => $this->name,
            'type' => $this->type,
        ];

        if ($this->description) {
            $config['description'] = $this->description->toConfig($this);
        }

        if (!empty($this->properties)) {
            $config['properties'] = ClassPropertyConfig::collectionToConfig($this->properties);
        }

        if (!empty($this->methods)) {
            $config['methods'] = ClassMethodConfig::collectionToConfig($this->methods);
        }

        return $config;
    }

    public function toCode(?AbstractConfig $parentConfig = null): string
    {
        $output = '';

        if ($this->description) {
            $output .= $this->description->toCode($this);
        }

        $output .= "class {$this->name}\n{\n";

        foreach ($this->properties as $property) {
            $output .= $property->toCode($this);
        }

        foreach ($this->methods as $method) {
            $output .= $method->toCode($this);
        }

        $output .= "}\n";

        return $output;
    }
}