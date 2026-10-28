<?php

namespace Wexample\Pseudocode\Tests\Item\Class;

use Wexample\Helpers\Testing\Traits\WithYamlTestCase;
use Wexample\Pseudocode\Config\ClassConfig;
use Wexample\Pseudocode\Tests\AbstractGeneratorTestCase;

class ClassItemTest extends AbstractGeneratorTestCase
{
    use WithYamlTestCase;

    protected function getItemType(): string
    {
        return ClassConfig::class;
    }

    /**
     * Test conversion of a basic class with properties and methods
     */
    public function testBasicClassToPseudocode(): void
    {
        $this->assertCodeToPseudocode('basic_calculator');
    }

    /**
     * Test conversion of a basic class with properties and methods
     */
    public function testBasicClassToCode(): void
    {
        $this->assertPseudocodeToCode('basic_calculator');
    }

    /**
     * A class extending an exported one says so. Read from code only: the
     * resource needs a namespace for its parent to be found, which the code
     * generated from pseudocode never carries.
     */
    public function testChildClassToPseudocode(): void
    {
        $this->assertCodeToPseudocode('child_shape');
    }
}
