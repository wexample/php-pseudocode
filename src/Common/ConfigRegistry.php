<?php

namespace Wexample\Pseudocode\Common;

use PhpParser\Node;
use Wexample\Pseudocode\Config\AbstractConfig;
use Wexample\Pseudocode\Config\ClassConfig;
use Wexample\Pseudocode\Config\ConstantConfig;
use Wexample\Pseudocode\Config\FunctionConfig;

class ConfigRegistry
{
    private array $registry = [];

    public function __construct()
    {
        // Register all available configs
        $this->register(ClassConfig::class);
        $this->register(FunctionConfig::class);
        $this->register(ConstantConfig::class);
    }

    public function register(string $configClass): void
    {
        $this->registry[] = $configClass;
    }

    /**
     * @param array $data
     * @return string|AbstractConfig
     */
    public function findMatchingConfigLoader(
        array $data
    ): ?string {
        /** @var AbstractConfig $config */
        foreach ($this->registry as $config) {
            if ($config::canLoad($data)) {
                return $config;
            }
        }

        return null;
    }

    /**
     * @param Node $node
     * @return string|AbstractConfig
     */
    public function findMatchingNodeParser(Node $node): ?string
    {
        /** @var AbstractConfig $config */
        foreach ($this->registry as $config) {
            if ($config::canParse($node)) {
                return $config;
            }
        }

        return null;
    }
}
