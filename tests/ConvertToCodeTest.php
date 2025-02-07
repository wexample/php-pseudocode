<?php

namespace Wexample\Pseudocode\Tests;

use Wexample\Pseudocode\Generator\CodeGenerator;

class ConvertToCodeTest extends AbstractConverterTest
{
    protected function getGenerator(): CodeGenerator
    {
        return new CodeGenerator();
    }

    public function testFullConversion(): void
    {
        // Load and convert YAML to Code
        $actualCode = $this->getGenerator()->generateCode(
            $this->loadExampleFileContent('yml')
        );

        // Load expected Code output
        $expectedPhp = $this->loadExampleFileContent('php');

        // Normalize line endings to prevent false negatives
        $actualCode = $this->normalizeLineEndings($actualCode);
        $expectedPhp = $this->normalizeLineEndings($expectedPhp);

        // Compare the entire output
        $this->assertEquals(
            $expectedPhp,
            $actualCode,
            "Generated code does not match expected output.\n" .
            "Expected:\n{$expectedPhp}\n" .
            "Actual:\n{$actualCode}"
        );
    }


}