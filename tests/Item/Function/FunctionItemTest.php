<?php

namespace Wexample\Pseudocode\Tests\Item\Function;

use Wexample\Helpers\Testing\Traits\WithYamlTestCase;
use Wexample\Pseudocode\Config\FunctionConfig;
use Wexample\Pseudocode\Generator\PseudocodeGenerator;
use Wexample\Pseudocode\Tests\AbstractGeneratorTest;

class FunctionItemTest extends AbstractGeneratorTest
{
    use WithYamlTestCase;

    protected function getItemType(): string
    {
        return FunctionConfig::class;
    }

    protected function getPseudocodeGenerator(): PseudocodeGenerator
    {
        return new PseudocodeGenerator();
    }

    /**
     * Test conversion of a basic function with parameters and return type
     */
    public function testBasicFunctionToPseudocode(): void
    {
        $this->assertCodeToPseudocode('basic_function');
    }

    /**
     * Test conversion of a function with complex parameters and PHPDoc
     */
    public function testComplexFunctionToPseudocode(): void
    {
        $this->assertCodeToPseudocode('complex_function');
    }

    /**
     * Test conversion of a basic function with parameters and return type
     */
    public function testBasicFunctionToCode(): void
    {
        $this->assertPseudocodeToCode('basic_function');
    }

    /**
     * Test conversion of a function with complex parameters and PHPDoc
     */
    public function testComplexFunctionToCode(): void
    {
        $this->assertPseudocodeToCode('complex_function');
    }
}
