<?php

namespace Wexample\Pseudocode\Testing;

use Wexample\Helpers\Testing\Traits\WithYamlTestCase;
use Wexample\Pseudocode\Generator\PseudocodeGenerator;

trait CodeToPseudocodeTestTrait
{
    use WithYamlTestCase;

    protected function assertCodeToPseudocode(string $filename): void
    {
        // Create temp directory if not exists
        $tempDir = sys_get_temp_dir() . '/pseudocode_tests';
        if (! is_dir($tempDir)) {
            mkdir($tempDir);
        }

        // Test PHP -> Pseudocode conversion
        $sourcePhp = $this->loadTestResource($filename . '.php');
        $actualPseudocodeData = $this->pseudocodeGenerator->generateConfigData($sourcePhp);

        $expectedYaml = $this->loadPseudocode($filename);
        $filteredExpected = $this->filterIgnoredKeys(['items' => $expectedYaml['items']]);
        $filteredActual = $this->filterIgnoredKeys($actualPseudocodeData);

        // Write YAML files for debugging
        file_put_contents(
            $tempDir . "/{$filename}_expected.yml",
            PseudocodeGenerator::dumpPseudocode($filteredExpected)
        );
        file_put_contents(
            $tempDir . "/{$filename}_actual.yml",
            PseudocodeGenerator::dumpPseudocode($filteredActual)
        );

        $this->assertArraysEqual(
            $filteredExpected,
            $filteredActual,
            "PHP to Pseudocode: Generated pseudocode does not match expected output for {$filename}.\n" .
            "Expected:\n" . json_encode($filteredExpected, JSON_PRETTY_PRINT) . "\n"
        );
    }
}
