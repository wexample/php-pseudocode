<?php

namespace Wexample\Pseudocode\Helper;

class ArrayHelper
{
    /**
     * Recursively compares two values (arrays or scalars) and returns the differences.
     *
     * When comparing arrays, if a key is missing in one of them but the corresponding value
     * in the other is an empty array, the difference is ignored when $allowEmptyMissing is true.
     *
     * @param mixed  $expected          The expected value (could be an array).
     * @param mixed  $actual            The actual value (could be an array).
     * @param string $path              The current path in the structure (for error reporting).
     * @param bool   $allowEmptyMissing If true, a missing key paired with an empty array is ignored.
     *
     * @return array List of differences as messages.
     */
    public static function diffArrays(
        mixed $expected,
        mixed $actual,
        string $path = '',
        bool $allowEmptyMissing = false
    ): array {
        $differences = [];

        if (is_array($expected) && is_array($actual)) {
            // Compare expected keys against actual values.
            foreach ($expected as $key => $expectedValue) {
                $currentPath = $path === '' ? $key : $path . '.' . $key;
                if (!array_key_exists($key, $actual)) {
                    // When allowed, ignore missing key if the expected value is an empty array.
                    if (!($allowEmptyMissing && is_array($expectedValue) && empty($expectedValue))) {
                        $differences[] = "Missing key in actual value: '$currentPath'";
                    }
                } else {
                    $differences = array_merge(
                        $differences,
                        self::diffArrays($expectedValue, $actual[$key], $currentPath, $allowEmptyMissing)
                    );
                }
            }
            // Look for extra keys present in actual but not in expected.
            foreach ($actual as $key => $actualValue) {
                $currentPath = $path === '' ? $key : $path . '.' . $key;
                if (!array_key_exists($key, $expected)) {
                    // When allowed, ignore extra key if the actual value is an empty array.
                    if (!($allowEmptyMissing && is_array($actualValue) && empty($actualValue))) {
                        $differences[] = "Extra key in actual value: '$currentPath'";
                    }
                }
            }
        } else {
            // For scalars or differing types, perform a direct comparison.
            if ($expected !== $actual) {
                $differences[] = "Difference at '$path': expected " . var_export($expected, true) . ", got " . var_export($actual, true);
            }
        }

        return $differences;
    }
}
