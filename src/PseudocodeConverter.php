<?php

namespace Wexample\Pseudocode;

use Symfony\Component\Yaml\Yaml;

class PseudocodeConverter
{
    private array $codeStructure;

    public function loadFromYaml(string $yamlContent): self
    {
        $this->codeStructure = Yaml::parse($yamlContent);
        return $this;
    }

    public function convert(): string
    {
        if (empty($this->codeStructure)) {
            throw new \RuntimeException('No code structure loaded. Call loadFromYaml() first.');
        }

        return $this->generatePhpCode($this->codeStructure);
    }

    private function generatePhpCode(array $structure): string
    {
        if (!isset($structure['items'])) {
            throw new \RuntimeException('Invalid structure: missing items array.');
        }

        $output = "<?php\n\n";

        foreach ($structure['items'] as $item) {
            if (!isset($item['type'])) {
                continue;
            }

            $output .= match($item['type']) {
                'constant' => $this->generateConstant($item),
                'function' => $this->generateFunction($item),
                'class' => $this->generateClass($item),
                default => ''
            };

            $output .= "\n";
        }

        return $output;
    }

    private function generateConstant(array $constant): string
    {
        $value = $this->formatValue($constant['value']);
        return sprintf(
            "define('%s', %s); // %s\n",
            $constant['name'],
            $value,
            $constant['description'] ?? ''
        );
    }

    private function generateFunction(array $function): string
    {
        $output = "";

        // Add function documentation
        if (isset($function['description'])) {
            $output .= "/**\n * " . $function['description'] . "\n";
            if (isset($function['parameters'])) {
                foreach ($function['parameters'] as $param) {
                    $output .= sprintf(
                        " * @param %s $%s %s\n",
                        $param['type'] ?? 'mixed',
                        $param['name'],
                        $param['description'] ?? ''
                    );
                }
            }
            if (isset($function['returnType'])) {
                $output .= " * @return " . $function['returnType'] . "\n";
            }
            $output .= " */\n";
        }

        $output .= sprintf("function %s(", $function['name']);

        // Parameters
        if (isset($function['parameters'])) {
            $params = array_map(
                fn($param) => sprintf(
                    '%s$%s',
                    isset($param['type']) ? $param['type'] . ' ' : '',
                    $param['name']
                ),
                $function['parameters']
            );
            $output .= implode(', ', $params);
        }

        $output .= ")" . (isset($function['returnType']) ? ": {$function['returnType']}" : "") . " {\n";

        // Implementation guidelines as comments
        if (isset($function['implementationGuidelines'])) {
            foreach (explode("\n", $function['implementationGuidelines']) as $line) {
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

    private function generateClass(array $class): string
    {
        $output = "";

        // Class documentation
        if (isset($class['description'])) {
            $output .= "/**\n * " . $class['description'] . "\n */\n";
        }

        $output .= "class {$class['name']} {\n";

        // Properties
        if (isset($class['properties'])) {
            foreach ($class['properties'] as $property) {
                if (isset($property['description'])) {
                    $output .= "    /** @var {$property['type']} {$property['description']} */\n";
                }
                $default = isset($property['default']) ? " = " . $this->formatValue($property['default']) : "";
                $output .= "    private {$property['type']} \${$property['name']}{$default};\n\n";
            }
        }

        // Methods
        if (isset($class['methods'])) {
            foreach ($class['methods'] as $method) {
                $output .= "    " . str_replace("\n", "\n    ", $this->generateFunction($method));
                $output .= "\n";
            }
        }

        $output .= "}\n";

        return $output;
    }

    private function formatValue(mixed $value): string
    {
        if (is_string($value)) {
            return '"' . addslashes($value) . '"';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_null($value)) {
            return 'null';
        }
        return (string)$value;
    }
}