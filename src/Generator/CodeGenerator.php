<?php

namespace Wexample\Pseudocode\Generator;

use Symfony\Component\Yaml\Yaml;
use Wexample\Helpers\Helper\FileHelper;
use Wexample\Pseudocode\Common\Traits\WithConfigRegistry;

class CodeGenerator extends AbstractGenerator
{
    use WithConfigRegistry;

    public function getSourceFileExtension(): string
    {
        return FileHelper::FILE_EXTENSION_YML;
    }

    public function getTargetFileExtension(): string
    {
        return FileHelper::FILE_EXTENSION_PHP;
    }

    public function generate(string $inputText): string
    {
        return $this->generateFromArray(Yaml::parse($inputText));
    }

    public function generateFromArray(array $pseudoCode): string
    {
        $output = "<?php\n\n";
        $registry = $this->getConfigRegistry();

        foreach ($pseudoCode['items'] as $data) {
            if ($configClass = $registry->findMatchingConfigLoader($data)) {
                $output .= $configClass->toCode() . PHP_EOL;
            }
        }

        return $output;
    }
}
