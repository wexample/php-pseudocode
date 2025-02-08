<?php

namespace Wexample\Pseudocode\Tests\Item\Function;

use Wexample\Pseudocode\Generator\PseudocodeGenerator;
use Wexample\Pseudocode\Item\FunctionItem;
use Wexample\Pseudocode\Testing\Traits\WithYamlTestCase;
use Wexample\Pseudocode\Tests\AbstractConverterTest;

class FunctionItemTest extends AbstractConverterTest
{
    use WithYamlTestCase;

    protected function getItemType(): string
    {
        return FunctionItem::class;
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
