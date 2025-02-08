<?php

namespace Wexample\Pseudocode\Tests\Item\Constant;

use Wexample\Pseudocode\Item\ConstantItem;
use Wexample\Pseudocode\Testing\Traits\WithYamlTestCase;
use Wexample\Pseudocode\Tests\AbstractGeneratorTest;

class ConstantItemTest extends AbstractGeneratorTest
{
    use WithYamlTestCase;

    protected function getItemType(): string
    {
        return ConstantItem::class;
    }

    /**
     * Test conversion of constants defined using define()
     */
    public function testDefineConstantConversion(): void
    {
        $this->assertConversion('constant_using_define');
    }

    /**
     * Test conversion of constants defined using const keyword
     */
    public function testConstKeywordConversion(): void
    {
        $this->assertConversion('constant_using_const');
    }
}
