<?php

namespace Wexample\Pseudocode\Tests;

use Wexample\Pseudocode\Generator\PseudocodeGenerator;

class ConvertToPseudocodeTest extends AbstractConverterTest
{
    protected function getGenerator(): PseudocodeGenerator
    {
        return new PseudocodeGenerator();
    }

    public function testFullConversion(): void
    {
        // Load and convert PHP to YAML
        $actualYaml = $this->generator->generatePseudocode(
            $this->loadExampleFileContent('php')
        );

        // Load expected YAML output
        $expectedYaml =  $this->loadExampleFileContent('yml');

        // Normalize line endings to prevent false negatives
        $actualYaml = $this->normalizeLineEndings($actualYaml);
        $expectedYaml = $this->normalizeLineEndings($expectedYaml);

        // Compare the entire output
        $this->assertEquals(
            $expectedYaml,
            $actualYaml,
            "Generated pseudocode does not match expected output.\n" .
            "Expected:\n{$expectedYaml}\n" .
            "Actual:\n{$actualYaml}"
        );
    }
}
