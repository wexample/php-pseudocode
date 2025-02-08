<?php

namespace Wexample\Pseudocode\Generator;

use Symfony\Component\Yaml\Yaml;
use Wexample\Helpers\Helper\FileHelper;

class CodeGenerator extends AbstractGenerator
{
    public function getSourceFileExtension(): string
    {
        return FileHelper::FILE_EXTENSION_YML;
    }

    public function getTargetFileExtension(): string
    {
        return FileHelper::FILE_EXTENSION_PHP;
    }

    public function generate(string $fileContent): string
    {
        $pseudoCode = Yaml::parse($fileContent);

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
