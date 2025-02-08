<?php

namespace Wexample\Pseudocode\Tests;

use Symfony\Component\Yaml\Yaml;
use Wexample\Pseudocode\Generator\PseudocodeGenerator;
use Wexample\Pseudocode\Testing\Traits\WithYamlTestCase;

class ConvertToPseudocodeTest extends AbstractConverterTest
{
    use WithYamlTestCase;

    protected function getGenerator(): PseudocodeGenerator
    {
        return new PseudocodeGenerator();
    }

    /**
     * List of keys to ignore when comparing YAML structures.
     * Add new keys here when needed.
     */
    private array $ignoredKeys = [
        'implementationGuidelines',
        // Add more keys to ignore here as needed
        // Example: 'someOtherKey',
    ];

    /**
     * Recursively removes specified keys from an array.
     */
    private function filterIgnoredKeys(array $data): array
    {
        foreach ($data as $key => $value) {
            // Remove ignored keys
            if (in_array($key, $this->ignoredKeys)) {
                unset($data[$key]);
                continue;
            }

            // Recursively process nested arrays
            if (is_array($value)) {
                $data[$key] = $this->filterIgnoredKeys($value);
            }
        }

        return $data;
    }

    public function testFullConversion(): void
    {
        // Load and convert PHP to YAML
        $generator = $this->getGenerator();
        $actualPseudocode = $generator->generateItems(
            $this->loadExampleFileContent('php')
        );

        // Load expected YAML output
        $expectedYaml = $this->loadExampleFileContent('yml');

        // Filter out ignored keys from both structures
        $filteredExpected = $this->filterIgnoredKeys(Yaml::parse($expectedYaml));

        // Compare the filtered output
        $this->assertArraysEquals(
            $filteredExpected,
            $actualPseudocode,
            "Generated pseudocode does not match expected output.\n" .
            "Expected:\n" . json_encode($filteredExpected, JSON_PRETTY_PRINT) . "\n" .
            "Actual:\n" . json_encode($actualPseudocode, JSON_PRETTY_PRINT)
        );
    }
}
