<?php

namespace Wexample\Pseudocode\Common\Traits;

use Wexample\Pseudocode\Common\ConfigRegistry;

trait WithConfigRegistry
{
    private ?ConfigRegistry $configRegistry = null;

    protected function getConfigRegistryClass(): string
    {
        return ConfigRegistry::class;
    }

    protected function getConfigRegistry(): ConfigRegistry
    {
        if (! $this->configRegistry) {
            $this->configRegistry = new ConfigRegistry();
        }

        return $this->configRegistry;
    }
}
