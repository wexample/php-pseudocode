<?php

namespace Wexample\Pseudocode\Helper;

use Symfony\Component\Yaml\Yaml;

class ArrayHelper {
    /**
     * Recursively sorts an array by its keys.
     *
     * @param array $array The array to sort.
     */
    public static function recursiveKsort(array &$array): void
    {
        ksort($array);
        foreach ($array as &$value) {
            if (is_array($value)) {
                self::recursiveKsort($value);
            }
        }
    }

    /**
     * Recursively compares two values (arrays or scalars) and returns the differences.
     *
     * @param mixed  $expected The expected value (could be an array).
     * @param mixed  $actual   The actual value (could be an array).
     * @param string $path     The current path in the structure (for error reporting).
     *
     * @return array List of differences as messages.
     */
    public static function diffArrays($expected, $actual, string $path = ''): array
    {
        $differences = [];

        if (is_array($expected) && is_array($actual)) {
            // Check that all expected keys exist and match.
            foreach ($expected as $key => $expectedValue) {
                $currentPath = ($path === '') ? $key : $path . '.' . $key;
                if (!array_key_exists($key, $actual)) {
                    $differences[] = "Missing key in actual value: '$currentPath'";
                } else {
                    $differences = array_merge(
                        $differences,
                        self::diffArrays($expectedValue, $actual[$key], $currentPath)
                    );
                }
            }
            // Check for additional keys in the actual array.
            foreach ($actual as $key => $actualValue) {
                $currentPath = ($path === '') ? $key : $path . '.' . $key;
                if (!array_key_exists($key, $expected)) {
                    $differences[] = "Extra key in actual value: '$currentPath'";
                }
            }
        } else {
            // Simple comparison for scalars or different types.
            if ($expected !== $actual) {
                $differences[] = "Difference at '$path': expected " . var_export($expected, true) . ", got " . var_export($actual, true);
            }
        }

        return $differences;
    }

    /**
     * Compares the parsed YAML content with a PHP array.
     * The keys order is ignored by sorting both arrays recursively.
     *
     * @param string $yamlContent The YAML content to parse and compare.
     * @param array  $arrayData   The expected PHP array.
     *
     * @return array List of differences. If empty, the structures are equivalent.
     */
    public static function diffYamlAndArray(string $yamlContent, array $arrayData): array
    {
        // Parse the YAML content.
        $parsedYaml = Yaml::parse($yamlContent);

        // Recursively sort both arrays to ignore key order.
        self::recursiveKsort($parsedYaml);
        self::recursiveKsort($arrayData);

        // Compare the two structures.
        return self::diffArrays($parsedYaml, $arrayData);
    }
}