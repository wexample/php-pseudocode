<?php

namespace Wexample\Pseudocode\Tests\Item\Class;

use Wexample\Pseudocode\Item\ClassItem;
use Wexample\Pseudocode\Testing\Traits\WithYamlTestCase;
use Wexample\Pseudocode\Tests\AbstractConverterTest;

class ClassItemTest extends AbstractConverterTest
{
    use WithYamlTestCase;

    protected function getItemType(): string
    {
        return ClassItem::class;
    }

    /**
     * Test conversion of a basic class with properties and methods
     */
    public function testBasicClassConversion(): void
    {
        $this->assertConversion('basic_calculator');
    }
}
