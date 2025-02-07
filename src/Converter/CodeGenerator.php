<?php

namespace Wexample\Pseudocode\Converter;

use Symfony\Component\Yaml\Yaml;
use Wexample\Pseudocode\Item\ItemFactory;
use Wexample\Pseudocode\Parser\PhpParser;

class CodeGenerator
{
    private ItemFactory $itemFactory;
    private PhpParser $phpParser;

    public function __construct()
    {
        $this->itemFactory = new ItemFactory();
        $this->phpParser = new PhpParser();
    }

    public function convertToCode(string $yamlContent): string
    {
        $data = Yaml::parse($yamlContent);
        
        if (!isset($data['items'])) {
            throw new \RuntimeException('Invalid structure: missing items array.');
        }

        $output = "<?php\n\n";
        
        foreach ($data['items'] as $itemData) {
            if (!isset($itemData['type'])) {
                continue;
            }

            $item = $this->itemFactory->createFromArray($itemData);
            $output .= $item->toPhp() . "\n";
        }

        return $output;
    }

    public function convertToPseudocode(string $phpCode): string
    {
        $items = $this->phpParser->parse($phpCode);
        
        $data = [
            'items' => $items
        ];

        return Yaml::dump($data, 4, 2);
    }
}
