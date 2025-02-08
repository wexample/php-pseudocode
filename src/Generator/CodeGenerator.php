<?php

namespace Wexample\Pseudocode\Generator;

use Symfony\Component\Yaml\Yaml;
use Wexample\Helpers\Helper\FileHelper;
use Wexample\Pseudocode\Config\AbstractConfig;
use Wexample\Pseudocode\Config\ClassConfig;
use Wexample\Pseudocode\Config\ConstantConfig;
use Wexample\Pseudocode\Config\FunctionConfig;

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

    public function generate(string $pseudocode): string
    {
        $pseudoCode = Yaml::parse($pseudocode);

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

    private function createFromConfigData(array $data): AbstractConfig
    {
        if (!isset($data['type'])) {
            throw new \InvalidArgumentException('Type is required');
        }

        return match ($data['type']) {
            'constant' => ConstantConfig::fromData($data),
            'function' => FunctionConfig::fromData($data),
            'class' => ClassConfig::fromData($data),
            default => throw new \InvalidArgumentException("Unknown item type: {$data['type']}")
        };
    }
}
