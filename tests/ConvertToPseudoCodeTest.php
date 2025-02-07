<?php

namespace Wexample\Pseudocode\Tests;

use PHPUnit\Framework\TestCase;
use Wexample\Pseudocode\Generator\CodeGenerator;

class ConvertToPseudoCodeTest extends TestCase
{
    private CodeGenerator $converter;
    private string $fixturesDir;

    protected function setUp(): void
    {
        $this->converter = new CodeGenerator();
        $this->fixturesDir = dirname(__DIR__);
    }

    public function testYamlToPhpConversion(): void
    {
        $yamlContent = file_get_contents($this->fixturesDir . '/tests/resources/example.yml');
        $expectedPhp = file_get_contents($this->fixturesDir . '/tests/resources/example.php');

        $actualPhp = $this->converter->convertToCode($yamlContent);

        $this->assertEquals(
            $this->normalizeCode($expectedPhp),
            $this->normalizeCode($actualPhp)
        );
    }

    public function testPhpToYamlConversion(): void
    {
        $phpContent = file_get_contents($this->fixturesDir . '/tests/resources/example.php');
        $expectedYaml = file_get_contents($this->fixturesDir . '/tests/resources/example.yml');

        $actualYaml = $this->converter->convertToPseudocode($phpContent);

        $this->assertEquals(
            $this->normalizeYaml($expectedYaml),
            $this->normalizeYaml($actualYaml)
        );
    }

    public function testBidirectionalConversion(): void
    {
        // YAML -> PHP -> YAML
        $originalYaml = file_get_contents($this->fixturesDir . '/tests/resources/example.yml');
        $php = $this->converter->convertToCode($originalYaml);
        $convertedYaml = $this->converter->convertToPseudocode($php);

        $this->assertEquals(
            $this->normalizeYaml($originalYaml),
            $this->normalizeYaml($convertedYaml)
        );

        // PHP -> YAML -> PHP
        $originalPhp = file_get_contents($this->fixturesDir . '/tests/resources/example.php');
        $yaml = $this->converter->convertToPseudocode($originalPhp);
        $convertedPhp = $this->converter->convertToCode($yaml);

        $this->assertEquals(
            $this->normalizeCode($originalPhp),
            $this->normalizeCode($convertedPhp)
        );
    }

    private function normalizeCode(string $code): string
    {
        // Convert all line endings to \n
        $code = str_replace("\r\n", "\n", $code);
        $code = str_replace("\r", "\n", $code);
        
        // Trim trailing whitespace
        $code = preg_replace('/[ \t]+$/m', '', $code);
        
        // Normalize empty lines between methods/functions
        $code = preg_replace('/\}\n[\n\s]*\/\*\*/m', "}\n\n/**", $code);
        
        // Normalize empty lines at the end of classes
        $code = preg_replace('/\}\n[\n\s]*\}/m', "}\n\n}", $code);
        
        // Remove any remaining multiple empty lines
        $code = preg_replace("/\n{3,}/", "\n\n", $code);
        
        // Ensure single newline at end of file
        return rtrim($code) . "\n";
    }

    private function normalizeYaml(string $yaml): string
    {
        // Parse and re-dump to normalize formatting
        $data = \Symfony\Component\Yaml\Yaml::parse($yaml);
        return \Symfony\Component\Yaml\Yaml::dump($data, 4, 2);
    }
}
