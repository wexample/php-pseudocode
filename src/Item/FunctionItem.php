<?php

namespace Wexample\Pseudocode\Item;

use PhpParser\Node;
use PhpParser\NodeAbstract;

class FunctionItem extends AbstractItem
{
    protected array $parameters;
    protected ?string $returnType;
    protected ?string $implementationGuidelines;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->parameters = $data['parameters'] ?? [];
        $this->returnType = $data['returnType'] ?? null;
        $this->implementationGuidelines = $data['implementationGuidelines'] ?? null;
    }

    public function generateCode(): string
    {
        $output = $this->generateDocBlock();
        $output .= $this->generateSignature();
        $output .= $this->generateBody();
        
        return $output;
    }

    protected function generateDocBlock(): string
    {
        $output = "/**\n * " . $this->description . "\n";
        
        foreach ($this->parameters as $param) {
            $output .= sprintf(
                " * @param %s $%s %s\n",
                $param['type'] ?? 'mixed',
                $param['name'],
                $param['description'] ?? ''
            );
        }
        
        if ($this->returnType) {
            $output .= " * @return " . $this->returnType . "\n";
        }
        
        $output .= " */\n";
        return $output;
    }

    protected function generateSignature(): string
    {
        $output = sprintf("function %s", $this->name);
        
        if (empty($this->parameters)) {
            return $output . "(): " . ($this->returnType ?? 'void') . "\n{\n";
        }

        $output .= "(\n";
        $params = array_map(
            fn($param) => sprintf(
                '    %s$%s',
                isset($param['type']) ? $param['type'] . ' ' : '',
                $param['name']
            ),
            $this->parameters
        );
        $output .= implode(",\n", $params);
        $output .= "\n): " . ($this->returnType ?? 'void') . "\n{\n";
        
        return $output;
    }

    protected function generateBody(): string
    {
        $output = '';
        
        if ($this->implementationGuidelines) {
            foreach (explode("\n", $this->implementationGuidelines) as $line) {
                $line = trim($line);
                if ($line) {
                    $output .= "    // " . $line . "\n";
                }
            }
        }
        
        $output .= "    // TODO: Implement function body\n";
        $output .= "}\n";
        
        return $output;
    }

    static function fromNode(NodeAbstract $node): array
    {
        return [
            'type' => 'function',
            'name' => $node->name->toString(),
            'description' => self::getDocComment($node),
            'parameters' => self::parseParameters($node->params),
            'returnType' => $node->returnType ? self::getTypeName($node->returnType) : null
        ];
    }
}
