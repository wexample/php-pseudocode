<?php

namespace Wexample\Pseudocode\Generator;

use Symfony\Component\Yaml\Yaml;

class CodeGenerator extends AbstractGenerator
{
    public function generateCode(string $yamlContent): string
    {
        $pseudoCode = Yaml::parse($yamlContent);

        if ($pseudoCode === null) {
            throw new \RuntimeException('No code structure loaded.');
        }

        $output = "<?php\n\n";

        foreach ($pseudoCode['items'] as $itemData) {
            if (!isset($itemData['type'])) {
                continue;
            }

            $item = $this->itemFactory->createFromArray($itemData);
            $output .= $item->generateCode() . "\n";
        }

        return $output;
    }
}
