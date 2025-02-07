<?php

namespace Wexample\Pseudocode\Testing\Traits;

use Wexample\Pseudocode\Helper\ArrayHelper;

trait WithYamlTestCase
{
    /**
     * Asserts that the given YAML content and PHP array are equivalent,
     * ignoring the order of keys.
     *
     * Example usage in a test:
     *
     * <code>
     * $yamlContent = file_get_contents('path/to/reference.yaml');
     * $generatedArray = yourFunctionThatGeneratesTheArray();
     * $this->assertYamlEqualsArray($yamlContent, $generatedArray, "Generated YAML does not match reference");
     * </code>
     *
     * @param string $yamlContent The expected YAML content.
     * @param array $arrayData The generated PHP array.
     * @param string $message Custom message for failure.
     */
    public function assertYamlEqualsArray(
        string $yamlContent,
        array $arrayData,
        string $message = ''
    ): void
    {
        $differences = ArrayHelper::diffYamlAndArray($yamlContent, $arrayData);
        if (!empty($differences)) {
            $fullMessage = $message . "\nDifferences found:\n" . implode("\n", $differences);
            $this->fail($fullMessage);
        }
        // If no differences are found, the assertion passes.
        $this->assertTrue(true);
    }
}