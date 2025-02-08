<?php

namespace Wexample\Pseudocode\Tests\Item\Function;

use Wexample\Pseudocode\Generator\PseudocodeGenerator;
use Wexample\Pseudocode\Tests\AbstractConverterTest;
use Wexample\Pseudocode\Testing\Traits\WithYamlTestCase;

class FunctionItemTest extends AbstractConverterTest
{
    use WithYamlTestCase;

    protected function getGenerator(): PseudocodeGenerator
    {
        return new PseudocodeGenerator();
    }

    /**
     * Test conversion of a basic function with parameters and return type
     */
    public function testBasicFunctionConversion(): void
    {
        $this->assertConversion('basic_function');
    }

    /**
     * Test conversion of a function with complex parameters and PHPDoc
     */
    public function testComplexFunctionConversion(): void
    {
        $this->assertConversion('complex_function');
    }

    /**
     * Helper method to test file conversion
     */
    protected function assertConversion(string $filename): void
    {
        $generator = $this->getGenerator();
        $actualPseudocode = $generator->generateItems(
            $this->loadTestResource($filename . '.php')
        );

        $expectedYaml = $this->loadTestResource($filename . '.yml');
        $filteredExpected = $this->filterIgnoredKeys(json_decode($expectedYaml, true));
        $filteredActual = $this->filterIgnoredKeys($actualPseudocode);

        $this->assertYamlEqualsArray(
            json_encode($filteredExpected),
            $filteredActual,
            "Generated pseudocode does not match expected output for {$filename}.\n" .
            "Expected:\n" . json_encode($filteredExpected, JSON_PRETTY_PRINT) . "\n" .
            "Actual:\n" . json_encode($filteredActual, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Load a test resource file
     */
    protected function loadTestResource(string $filename): string
    {
        return file_get_contents(__DIR__ . '/resources/' . $filename);
    }

    /**
     * List of keys to ignore when comparing YAML structures
     */
    private array $ignoredKeys = [
        'implementationGuidelines',
    ];

    /**
     * Recursively removes specified keys from an array
     */
    private function filterIgnoredKeys(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->ignoredKeys)) {
                unset($data[$key]);
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->filterIgnoredKeys($value);
            }
        }

        return $data;
    }
}
