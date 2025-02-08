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

    protected function getGenerator(): PseudocodeGenerator
    {
        return new PseudocodeGenerator();
    }

    /**
     * Test conversion of a basic function with parameters and return type
     */
    public function testBasicFunctionConversion(): void
    {
        $this->assertConversion('basic_function');
    }

    /**
     * Test conversion of a function with complex parameters and PHPDoc
     */
    public function testComplexFunctionConversion(): void
    {
        $this->assertConversion('complex_function');
    }
}
