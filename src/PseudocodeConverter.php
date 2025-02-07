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
        $output = '';
        
        // Pour l'instant, on gère uniquement une structure simple de fonction
        if (isset($structure['function'])) {
            $function = $structure['function'];
            $output .= sprintf("function %s(", $function['name'] ?? 'unnamed');
            
            // Gestion des paramètres
            if (isset($function['parameters'])) {
                $output .= implode(', ', array_map(
                    fn($param) => '$' . $param,
                    $function['parameters']
                ));
            }
            
            $output .= ") {\n";
            
            // Corps de la fonction
            if (isset($function['body'])) {
                $output .= "    " . implode("\n    ", (array)$function['body']) . "\n";
            }
            
            $output .= "}\n";
        }

        return $output;
    }
}
