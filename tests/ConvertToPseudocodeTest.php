<?php

namespace Wexample\Pseudocode\Tests;

use Wexample\Pseudocode\Generator\PseudocodeGenerator;
use Wexample\Pseudocode\Testing\Traits\WithYamlTestCase;

class ConvertToPseudocodeTest extends AbstractConverterTest
{
    use WithYamlTestCase;

    protected function getGenerator(): PseudocodeGenerator
    {
        return new PseudocodeGenerator();
    }

    public function testFullConversion(): void
    {
        // Load and convert PHP to YAML
        $generator = $this->getGenerator();
        $actualPseudocode = $generator->generateItems(
            $this->loadExampleFileContent('php')
        );

        // Load expected YAML output
        $expectedYaml = $this->loadExampleFileContent('yml');

        // Compare the entire output
        $this->assertYamlEqualsArray(
            $expectedYaml,
            $actualPseudocode,
            "Generated pseudocode does not match expected output.\n" .
            "Expected:\n{$expectedYaml}\n" .
            "Actual:\n{$generator->dumpItems($actualPseudocode)}"
        );
    }
}
