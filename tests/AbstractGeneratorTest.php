<?php

namespace Wexample\Pseudocode\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Wexample\Helpers\Testing\Traits\WithYamlTestCase;
use Wexample\Pseudocode\Generator\AbstractGenerator;
use Wexample\Pseudocode\Generator\PseudocodeGenerator;
use Wexample\Pseudocode\Config\AbstractConfig;

abstract class AbstractGeneratorTest extends TestCase
{
    use WithYamlTestCase;

    protected AbstractGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = $this->getGenerator();
    }

    protected function getGenerator(): PseudocodeGenerator
    {
        return new PseudocodeGenerator();
    }

    /**
     * @return class-string<AbstractConfig>
     */
    abstract protected function getItemType(): string;

    /**
     * Helper method to test file conversion
     */
    protected function assertConversion(string $filename): void
    {
        $generator = $this->getGenerator();
        $actualPseudocode = $generator->generateConfigData(
            $this->loadTestResource($filename . '.php')
        );

        $expectedYaml = $this->loadPseudocode($filename);
        $filteredExpected = $this->filterIgnoredKeys($expectedYaml);
        $filteredActual = $this->filterIgnoredKeys($actualPseudocode);

        $this->assertArraysEqual(
            $filteredExpected,
            $filteredActual,
            "Generated pseudocode does not match expected output for {$filename}.\n" .
            "Expected:\n" . json_encode($filteredExpected, JSON_PRETTY_PRINT) . "\n" .
            "Actual:\n" . json_encode($filteredActual, JSON_PRETTY_PRINT)
        );
    }

    protected function loadPseudocode(string $filename): array
    {
        return Yaml::parse($this->loadTestResource($filename . '.yml'));
    }

    /**
     * Load a test resource file
     */
    protected function loadTestResource(string $filename): string
    {
        $path = __DIR__ . '/Item/' . $this->getItemType()::getShortClassName() . '/resources/' . $filename;

        if (!file_exists($path)) {
            throw new \RuntimeException("Test resource not found: {$path}");
        }
        return file_get_contents($path);
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
