<?php

namespace Wexample\Pseudocode;

use Symfony\Component\Yaml\Yaml;
use Wexample\Pseudocode\Item\ItemFactory;

class PseudocodeGenerator
{
    private array $codeStructure;
    private ItemFactory $itemFactory;

    public function __construct()
    {
        $this->itemFactory = new ItemFactory();
    }

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

        foreach ($structure['items'] as $itemData) {
            if (!isset($itemData['type'])) {
                continue;
            }

            $item = $this->itemFactory->createFromArray($itemData);
            $output .= $item->generateCode() . "\n";
        }

        return $output;
    }
}