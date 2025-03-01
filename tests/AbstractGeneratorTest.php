<?php

namespace Wexample\Pseudocode\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Wexample\Helpers\Testing\Traits\WithYamlTestCase;
use Wexample\Pseudocode\Config\AbstractConfig;
use Wexample\Pseudocode\Generator\CodeGenerator;
use Wexample\Pseudocode\Generator\PseudocodeGenerator;
use Wexample\Pseudocode\Testing\CodeToPseudocodeTestTrait;
use Wexample\Pseudocode\Testing\PseudocodeToCodeTestTrait;

abstract class AbstractGeneratorTest extends TestCase
{
    use CodeToPseudocodeTestTrait;
    use PseudocodeToCodeTestTrait;

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
     * Helper method to assert arrays are equal
     */
    protected function assertArraysEqual(
        array $expected,
        array $actual,
        string $message = ''
    ): void
    {
        $this->assertEquals($expected, $actual, $message);
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
