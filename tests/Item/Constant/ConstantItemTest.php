<?php

namespace Wexample\Pseudocode\Tests\Item\Constant;

use Wexample\Pseudocode\Generator\PseudocodeGenerator;
use Wexample\Pseudocode\Tests\AbstractConverterTest;
use Wexample\Pseudocode\Testing\Traits\WithYamlTestCase;

class ConstantItemTest extends AbstractConverterTest
{
    use WithYamlTestCase;

    protected function getGenerator(): PseudocodeGenerator
    {
        return new PseudocodeGenerator();
    }

    /**
     * Test conversion of constants defined using define()
     */
    public function testDefineConstantConversion(): void
    {
        $this->assertConversion('constant_using_define');
    }

    /**
     * Test conversion of constants defined using const keyword
     */
    public function testConstKeywordConversion(): void
    {
        $this->assertConversion('constant_using_const');
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
