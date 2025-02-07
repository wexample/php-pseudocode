<?php

namespace Wexample\Pseudocode\Generator;

use Symfony\Component\Yaml\Yaml;
use Wexample\Pseudocode\Parser\PhpParser;

class CodeGenerator extends AbstractGenerator
{
    private PhpParser $phpParser;

    public function __construct()
    {
        parent::__construct();
        $this->phpParser = new PhpParser();
    }

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
