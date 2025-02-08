<?php

namespace Wexample\Pseudocode\Testing\Traits;

use SebastianBergmann\Diff\Differ;
use Symfony\Component\Yaml\Yaml;
use Wexample\Pseudocode\Helper\ArrayHelper;

trait WithYamlTestCase
{
    /**
     * Asserts that the contents of two YAML files are equal.
     * The YAML files are parsed into arrays before comparison.
     *
     * @param string $expectedFilePath Path to the expected YAML file.
     * @param string $actualFilePath   Path to the generated YAML file.
     * @param string $message          Optional message on failure.
     *
     * @return void
     */
    protected function assertYamlFilesEqual(
        string $expectedFilePath,
        string $actualFilePath,
        string $message = ''
    ): void {
        $expectedArray = Yaml::parse(file_get_contents($expectedFilePath));
        $actualArray = Yaml::parse(file_get_contents($actualFilePath));
        $this->assertArraysEqual($expectedArray, $actualArray, $message);
    }

    /**
     * Asserts that two arrays are equal.
     * This method works exclusively with arrays and produces a unified diff
     * if any differences are found.
     *
     * @param array  $expected The expected array.
     * @param array  $actual   The actual array.
     * @param string $message  Optional message on failure.
     *
     * @return void
     */
    protected function assertArraysEqual(
        array $expected,
        array $actual,
        string $message = ''
    ): void {
        $differences = ArrayHelper::diffArrays($expected, $actual);
        if (!empty($differences)) {
            // Convert arrays to strings for a consistent and comparable representation.
            $expectedString = var_export($expected, true);
            $actualString   = var_export($actual, true);

            $differ = new Differ();
            $diff   = $differ->diff($expectedString, $actualString);

            $fullMessage = $message . "\nDifferences found:\n" . $diff;
            $this->fail($fullMessage);
        }

        $this->assertTrue(true);
    }
}
