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

    public function testSimpleFunctionConversion(): void
    {
        $yamlContent = file_get_contents(__DIR__ . '/example.yml');
        
        $expectedPhp = <<<'PHP'
function calculateSum($a, $b) {
    return $a + $b;
}

PHP;

        $actualPhp = $this->converter
            ->loadFromYaml($yamlContent)
            ->convert();

        $this->assertEquals($expectedPhp, $actualPhp);
    }
}
