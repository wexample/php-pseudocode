<?php

namespace Wexample\Pseudocode\Common;

use PhpParser\Node;
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
     * @param Node $node
     * @return string|AbstractConfig
     */
    public function findMatchingConfig(Node $node): ?string
    {
        foreach ($this->registry as $config) {
            if ($config::canParse($node)) {
                return $config;
            }
        }

        return null;
    }
}
