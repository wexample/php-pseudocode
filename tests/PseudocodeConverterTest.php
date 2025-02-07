<?php

namespace Wexample\Pseudocode\Tests;

use PHPUnit\Framework\TestCase;
use Wexample\Pseudocode\PseudocodeConverter;

class PseudocodeConverterTest extends TestCase
{
    private PseudocodeConverter $converter;
    private string $fixturesDir;

    protected function setUp(): void
    {
        $this->converter = new PseudocodeConverter();
        $this->fixturesDir = __DIR__;
    }

    public function testFullConversion(): void
    {
        // Load and convert YAML to PHP
        $yamlContent = file_get_contents($this->fixturesDir . '/example.yml');
        $actualPhp = $this->converter->loadFromYaml($yamlContent)->convert();

        // Load expected PHP output
        $expectedPhp = file_get_contents($this->fixturesDir . '/expected/example.php');

        // Normalize line endings to prevent false negatives
        $actualPhp = $this->normalizeLineEndings($actualPhp);
        $expectedPhp = $this->normalizeLineEndings($expectedPhp);

        // Compare the entire output
        $this->assertEquals(
            $expectedPhp,
            $actualPhp,
            "Generated PHP code does not match expected output.\n" .
            "Expected:\n{$expectedPhp}\n" .
            "Actual:\n{$actualPhp}"
        );
    }

    /**
     * Normalizes line endings to prevent false negatives in tests
     * due to different operating systems
     */
    private function normalizeLineEndings(string $content): string
    {
        // Convert all line endings to \n
        $content = str_replace("\r\n", "\n", $content);
        $content = str_replace("\r", "\n", $content);
        
        // Ensure only single line breaks
        $content = preg_replace("/\n+/", "\n", $content);
        
        // Trim trailing whitespace
        $content = preg_replace('/[ \t]+$/m', '', $content);
        
        // Ensure single newline at end of file
        return rtrim($content) . "\n";
    }
}