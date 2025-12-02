<?php

namespace Wexample\Pseudocode\Testing;

use Wexample\Pseudocode\Generator\PseudocodeGenerator;

trait PseudocodeToCodeTestTrait
{
    protected function assertPseudocodeToCode(string $filename): void
    {
        // Create temp directory if not exists
        $tempDir = sys_get_temp_dir() . '/pseudocode_tests';
        if (! is_dir($tempDir)) {
            mkdir($tempDir);
        }

        $sourcePhp = $this->loadTestResource($filename . '.php');
        $expectedYaml = $this->loadPseudocode($filename);

        // Test Pseudocode -> PHP conversion
        // We use the test yaml instead of the generated one to allow passing
        // custom generator configuration which can't be generated from code.
        $regeneratedPhp = $this->codeGenerator->generate(
            PseudocodeGenerator::dumpPseudocode($expectedYaml)
        );

        // Write PHP files for debugging
        file_put_contents(
            $tempDir . "/{$filename}_original.php",
            $sourcePhp
        );
        file_put_contents(
            $tempDir . "/{$filename}_regenerated.php",
            $regeneratedPhp
        );

        // Normalize both codes to compare them
        $normalizedOriginal = $this->normalizeCode($sourcePhp);
        $normalizedRegenerated = $this->normalizeCode($regeneratedPhp);

        // Write normalized PHP files for debugging
        file_put_contents(
            $tempDir . "/{$filename}_original_normalized.php",
            $normalizedOriginal
        );
        file_put_contents(
            $tempDir . "/{$filename}_regenerated_normalized.php",
            $normalizedRegenerated
        );

        $this->assertEquals(
            $normalizedOriginal,
            $normalizedRegenerated,
            "Pseudocode to PHP: Generated PHP code does not match original for {$filename}.\n" .
            "Files written to: {$tempDir}"
        );
    }
}
