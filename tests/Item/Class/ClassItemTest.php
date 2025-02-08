<?php

namespace Wexample\Pseudocode\Tests\Item\Class;

use Wexample\Helpers\Testing\Traits\WithYamlTestCase;
use Wexample\Pseudocode\Config\ClassConfig;
use Wexample\Pseudocode\Tests\AbstractGeneratorTest;

class ClassItemTest extends AbstractGeneratorTest
{
    use WithYamlTestCase;

    protected function getItemType(): string
    {
        return ClassConfig::class;
    }

    /**
     * Test conversion of a basic class with properties and methods
     */
    public function testBasicClassConversion(): void
    {
        $this->assertConversion('basic_calculator');
    }
}
