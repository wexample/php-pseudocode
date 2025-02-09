<?php

namespace Wexample\Pseudocode\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Wexample\Helpers\Testing\Traits\WithYamlTestCase;
use Wexample\Pseudocode\Config\AbstractConfig;
use Wexample\Pseudocode\Generator\CodeGenerator;
use Wexample\Pseudocode\Generator\PseudocodeGenerator;

abstract class AbstractGeneratorTest extends TestCase
{
    use WithYamlTestCase;

    protected PseudocodeGenerator $pseudocodeGenerator;
    protected CodeGenerator $codeGenerator;

    protected function setUp(): void
    {
        $this->pseudocodeGenerator = new PseudocodeGenerator();
        $this->codeGenerator = new CodeGenerator();
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
        // Create temp directory if not exists
        $tempDir = sys_get_temp_dir() . '/pseudocode_tests';
        if (!is_dir($tempDir)) {
            mkdir($tempDir);
        }

        // Test PHP -> Pseudocode conversion
        $sourcePhp = $this->loadTestResource($filename . '.php');
        $actualPseudocodeData = $this->pseudocodeGenerator->generateConfigData($sourcePhp);

        $expectedYaml = $this->loadPseudocode($filename);
        $filteredExpected = $this->filterIgnoredKeys(['items' => $expectedYaml['items']]);
        $filteredActual = $this->filterIgnoredKeys($actualPseudocodeData);

        // Write YAML files
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

        // Test Pseudocode -> PHP conversion
        $regeneratedPhp = $this->codeGenerator->generate(
            PseudocodeGenerator::dumpPseudocode($actualPseudocodeData)
        );

        // Write PHP files
        file_put_contents(
            $tempDir . "/{$filename}_original.php",
            $sourcePhp
        );
        file_put_contents(
            $tempDir . "/{$filename}_regenerated.php",
            $regeneratedPhp
        );

        // Normalize both codes to compare them
        $normalizedOriginal = $this->normalizeCode($sourcePhp);
        $normalizedRegenerated = $this->normalizeCode($regeneratedPhp);

        // Write normalized PHP files
        file_put_contents(
            $tempDir . "/{$filename}_original_normalized.php",
            $normalizedOriginal
        );
        file_put_contents(
            $tempDir . "/{$filename}_regenerated_normalized.php",
            $normalizedRegenerated
        );

        $this->assertEquals(
            $normalizedOriginal,
            $normalizedRegenerated,
            "Pseudocode to PHP: Generated PHP code does not match original for {$filename}.\n" .
            "Files written to: {$tempDir}"
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
        'generator',
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
