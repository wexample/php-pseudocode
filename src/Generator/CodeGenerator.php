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
        $configs = $this->generateConfigInstances($inputText);
        $output = "<?php\n\n";

        foreach ($configs as $config) {
            $output .= $config->toCode() . PHP_EOL;
        }
        return $output;
    }

    protected function generateConfigInstances(string $inputText): array
    {
        $data = Yaml::parse($inputText);
        $registry = $this->getConfigRegistry();
        $instances = [];

        foreach ($data['items'] as $data) {
            if ($configClass = $registry->findMatchingConfigLoader($data)) {
                print_r(($configClass));
                $instances[] = $configClass::fromConfig($data);
            }
        }

        return $instances;
    }
}
