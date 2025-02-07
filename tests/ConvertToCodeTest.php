<?php

namespace Wexample\Pseudocode\Tests;

use PHPUnit\Framework\TestCase;
use Wexample\Pseudocode\Generator\CodeGenerator;

class ConvertToCodeTest extends TestCase
{
    private CodeGenerator $converter;
    private string $fixturesDir;

    protected function setUp(): void
    {
        $this->converter = new CodeGenerator();
        $this->fixturesDir = __DIR__;
    }

    public function testFullConversion(): void
    {
        // Load and convert YAML to Code
        $actualCode = $this->converter->generateCode(
            file_get_contents($this->fixturesDir . '/resources/example.yml')
        );

        // Load expected Code output
        $expectedPhp = file_get_contents($this->fixturesDir . '/resources/example.php');

        // Normalize line endings to prevent false negatives
        $actualCode = $this->normalizeLineEndings($actualCode);
        $expectedPhp = $this->normalizeLineEndings($expectedPhp);

        // Compare the entire output
        $this->assertEquals(
            $expectedPhp,
            $actualCode,
            "Generated code does not match expected output.\n" .
            "Expected:\n{$expectedPhp}\n" .
            "Actual:\n{$actualCode}"
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
        
        // Trim trailing whitespace
        $content = preg_replace('/[ \t]+$/m', '', $content);
        
        // Normalize empty lines between methods/functions
        $content = preg_replace('/\}\n[\n\s]*\/\*\*/m', "}\n\n/**", $content);
        
        // Normalize empty lines at the end of classes
        $content = preg_replace('/\}\n[\n\s]*\}/m', "}\n\n}", $content);
        
        // Remove any remaining multiple empty lines
        $content = preg_replace("/\n{3,}/", "\n\n", $content);
        
        // Ensure single newline at end of file
        return rtrim($content) . "\n";
    }
}