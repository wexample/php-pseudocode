<?php

namespace Wexample\Pseudocode\Generator;

use Symfony\Component\Yaml\Yaml;
use Wexample\Helpers\Helper\FileHelper;
use Wexample\Pseudocode\Common\Traits\WithConfigRegistry;
use Wexample\Pseudocode\Config\GeneratorConfig;

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
        $configs = $this->generateConfig($inputText);
        $output = "<?php\n\n";

        foreach ($configs as $config) {
            $output .= $config->toCode() . PHP_EOL;
        }
        return $output;
    }

    protected function generateConfig(string $inputText): array
    {
        $data = Yaml::parse($inputText);
        $registry = $this->getConfigRegistry();
        $instances = [];

        $globalGeneratorConfig = null;
        if (isset($data['generator'])) {
            $globalGeneratorConfig = GeneratorConfig::fromConfig($data['generator']);
        }

        foreach ($data['items'] as $data) {
            if ($configClass = $registry->findMatchingConfigLoader($data)) {
                $instances[] = $configClass::fromConfig(
                    $data,
                    $globalGeneratorConfig
                );
            }
        }

        return $instances;
    }
}
