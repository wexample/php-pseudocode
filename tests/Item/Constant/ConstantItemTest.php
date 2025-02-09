<?php

namespace Wexample\Pseudocode\Tests\Item\Constant;

use Wexample\Helpers\Testing\Traits\WithYamlTestCase;
use Wexample\Pseudocode\Config\ConstantConfig;
use Wexample\Pseudocode\Tests\AbstractGeneratorTest;

class ConstantItemTest extends AbstractGeneratorTest
{
    use WithYamlTestCase;

    protected function getItemType(): string
    {
        return ConstantConfig::class;
    }

    /**
     * Test conversion of constants defined using define()
     */
    public function testConstantDefineToPseudocode(): void
    {
        $this->assertCodeToPseudocode('constant_using_define');
    }

    /**
     * Test conversion of constants defined using const keyword
     */
    public function testConstantConstKeywordPseudocode(): void
    {
        $this->assertCodeToPseudocode('constant_using_const');
    }

    /**
     * Test conversion of constants defined using define()
     */
    public function testConstantDefineToCode(): void
    {
        $this->assertPseudocodeToCode('constant_using_define');
    }

    /**
     * Test conversion of constants defined using const keyword
     */
    public function testConstantConstKeywordToCode(): void
    {
        $this->assertPseudocodeToCode('constant_using_const');
    }
}
