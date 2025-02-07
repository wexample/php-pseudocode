<?php

namespace Wexample\Pseudocode\Item;

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
