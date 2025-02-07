<?php

namespace Wexample\Pseudocode\Tests\Converter;

use PHPUnit\Framework\TestCase;
use Wexample\Pseudocode\Converter\CodeConverter;

class CodeConverterTest extends TestCase
{
    private CodeConverter $converter;
    private string $fixturesDir;

    protected function setUp(): void
    {
        $this->converter = new CodeConverter();
        $this->fixturesDir = dirname(__DIR__);
    }

    public function testYamlToPhpConversion(): void
    {
        $yamlContent = file_get_contents($this->fixturesDir . '/example.yml');
        $expectedPhp = file_get_contents($this->fixturesDir . '/expected/example.php');

        $actualPhp = $this->converter->convertYamlToPhp($yamlContent);

        $this->assertEquals(
            $this->normalizeCode($expectedPhp),
            $this->normalizeCode($actualPhp)
        );
    }

    public function testPhpToYamlConversion(): void
    {
        $phpContent = file_get_contents($this->fixturesDir . '/expected/example.php');
        $expectedYaml = file_get_contents($this->fixturesDir . '/example.yml');

        $actualYaml = $this->converter->convertPhpToYaml($phpContent);

        $this->assertEquals(
            $this->normalizeYaml($expectedYaml),
            $this->normalizeYaml($actualYaml)
        );
    }

    public function testBidirectionalConversion(): void
    {
        // YAML -> PHP -> YAML
        $originalYaml = file_get_contents($this->fixturesDir . '/example.yml');
        $php = $this->converter->convertYamlToPhp($originalYaml);
        $convertedYaml = $this->converter->convertPhpToYaml($php);

        $this->assertEquals(
            $this->normalizeYaml($originalYaml),
            $this->normalizeYaml($convertedYaml)
        );

        // PHP -> YAML -> PHP
        $originalPhp = file_get_contents($this->fixturesDir . '/expected/example.php');
        $yaml = $this->converter->convertPhpToYaml($originalPhp);
        $convertedPhp = $this->converter->convertYamlToPhp($yaml);

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
