<?php

namespace Wexample\Pseudocode\Tests;

use PHPUnit\Framework\TestCase;
use Wexample\Pseudocode\PseudocodeConverter;

class PseudocodeConverterTest extends TestCase
{
    private PseudocodeConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new PseudocodeConverter();
    }

    public function testFullConversion(): void
    {
        $yamlContent = file_get_contents(__DIR__ . '/example.yml');
        $php = $this->converter->loadFromYaml($yamlContent)->convert();

        // Test constants
        $this->assertStringContainsString("define('PI', 3.14159);", $php);
        $this->assertStringContainsString("define('DEFAULT_GREETING', \"Hello, World!\");", $php);

        // Test function
        $this->assertStringContainsString('function calculateSum(', $php);
        $this->assertStringContainsString('number $a', $php);
        $this->assertStringContainsString('number $b', $php);

        // Test class
        $this->assertStringContainsString('class Calculator', $php);
        $this->assertStringContainsString('private number $lastResult = 0;', $php);
        $this->assertStringContainsString('function add(', $php);
        $this->assertStringContainsString('function reset(', $php);
    }
}