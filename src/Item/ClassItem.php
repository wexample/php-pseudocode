<?php

namespace Wexample\Pseudocode\Item;

use PhpParser\Node;
use PhpParser\NodeAbstract;

class ClassItem extends AbstractItem
{
    protected array $properties;
    protected array $methods;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->properties = $data['properties'] ?? [];
        $this->methods = $data['methods'] ?? [];
    }


    public static function fromNode(NodeAbstract $node): array
    {
        $properties = [];
        $methods = [];

        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Property) {
                $properties[] = static::parseProperty($stmt);
            } elseif ($stmt instanceof Node\Stmt\ClassMethod) {
                $methods[] = static::parseMethod($stmt);
            }
        }

        return [
            'name' => $node->name->toString(),
            'type' => 'class',
            'description' => static::getDocComment($node),
            'properties' => $properties,
            'methods' => $methods
        ];
    }

    private static function parseProperty(Node\Stmt\Property $node): array
    {
        $docInfo = self::parseDocComment($node);
        
        // Check for @var tag which might contain a better description
        $description = $docInfo['description'];
        if (preg_match('/@var\s+\S+(?:\s+([^*\/]+))?/', $node->getDocComment()?->getText() ?? '', $matches)) {
            if (!empty($matches[1])) {
                $description = trim($matches[1]);
            }
        }
        
        return [
            'name' => $node->props[0]->name->toString(),
            'type' => self::getTypeName($node->type),
            'description' => $description,
            'default' => $node->props[0]->default ? self::parseValue($node->props[0]->default) : null
        ];
    }

    private static function parseMethod(Node\Stmt\ClassMethod $node): array
    {
        $functionData = FunctionItem::fromNode($node);
        unset($functionData['type']); // Remove function type as it's a method
        return $functionData;
    }

    public function generateCode(): string
    {
        $output = $this->formatDocBlock($this->description);
        $output .= "class {$this->name}\n{\n";
        $output .= $this->generateProperties();
        $output .= $this->generateMethods();
        $output .= "}\n";

        return $output;
    }

    protected function generateProperties(): string
    {
        $output = '';

        foreach ($this->properties as $property) {
            if (isset($property['description'])) {
                $output .= "    /** @var {$property['type']} {$property['description']} */\n";
            }

            $default = isset($property['default'])
                ? " = " . $this->formatValue($property['default'])
                : "";

            $output .= "    private {$property['type']} \${$property['name']}{$default};\n\n";
        }

        return $output;
    }

    protected function generateMethods(): string
    {
        $output = '';

        foreach ($this->methods as $methodData) {
            $method = new FunctionItem($methodData);
            $output .= "    " . str_replace("\n", "\n    ", $method->generateCode());
            $output .= "\n";
        }

        return $output;
    }
}
