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
        // Test PHP -> Pseudocode conversion
        $generator = $this->getGenerator();
        $sourcePhp = $this->loadTestResource($filename . '.php');
        $actualPseudocode = $generator->generateConfigData($sourcePhp);

        $expectedYaml = $this->loadPseudocode($filename);
        $filteredExpected = $this->filterIgnoredKeys($expectedYaml);
        $filteredActual = $this->filterIgnoredKeys($actualPseudocode);

        $this->assertArraysEqual(
            $filteredExpected,
            $filteredActual,
            "PHP to Pseudocode: Generated pseudocode does not match expected output for {$filename}.\n" .
            "Expected:\n" . json_encode($filteredExpected, JSON_PRETTY_PRINT) . "\n"
        );

        // Test Pseudocode -> PHP conversion
        $configClass = $this->getItemType();
        $config = $configClass::fromConfig($actualPseudocode);
        $regeneratedPhp = $config->toCode();

        // Normalize both codes to compare them
        $normalizedOriginal = $this->normalizeCode($sourcePhp);
        $normalizedRegenerated = $this->normalizeCode($regeneratedPhp);

        $this->assertEquals(
            $normalizedOriginal,
            $normalizedRegenerated,
            "Pseudocode to PHP: Generated PHP code does not match original for {$filename}."
        );
    }

    /**
     * Normalize code by removing extra whitespace and empty lines
     */
    private function normalizeCode(string $code): string
    {
        // Remove comments
        $code = preg_replace('/\/\*.*?\*\//s', '', $code);
        $code = preg_replace('/\/\/.*$/m', '', $code);
        
        // Split into lines
        $lines = explode("\n", $code);
        
        // Remove empty lines and trim each line
        $lines = array_filter(array_map('trim', $lines));
        
        // Rejoin and normalize whitespace
        return preg_replace('/\s+/', ' ', implode("\n", $lines));
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
