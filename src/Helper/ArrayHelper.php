<?php

namespace Wexample\Pseudocode\Helper;

class ArrayHelper
{
    /**
     * Recursively compares two values (arrays or scalars) and returns true if they are identical,
     * false otherwise.
     *
     * When comparing arrays, if a key is missing in one of them while the corresponding value
     * in the other is an empty array, this difference is ignored when $allowEmptyMissing is true.
     *
     * @param mixed $expected          The expected value (can be an array or a scalar).
     * @param mixed $actual            The actual value (can be an array or a scalar).
     * @param bool  $allowEmptyMissing If true, a missing key paired with an empty array is ignored.
     *
     * @return bool True if $expected and $actual are identical, false otherwise.
     */
    public static function areSame(mixed $expected, mixed $actual, bool $allowEmptyMissing = false): bool
    {
        if (is_array($expected) && is_array($actual)) {
            // Compare expected keys with actual values.
            foreach ($expected as $key => $expectedValue) {
                if (!array_key_exists($key, $actual)) {
                    // If allowed, ignore the missing key if the expected value is an empty array.
                    if (!($allowEmptyMissing && is_array($expectedValue) && empty($expectedValue))) {
                        return false;
                    }
                } else {
                    if (!self::areSame($expectedValue, $actual[$key], $allowEmptyMissing)) {
                        return false;
                    }
                }
            }
            // Check for extra keys present in $actual but not in $expected.
            foreach ($actual as $key => $actualValue) {
                if (!array_key_exists($key, $expected)) {
                    // If allowed, ignore the extra key if the actual value is an empty array.
                    if (!($allowEmptyMissing && is_array($actualValue) && empty($actualValue))) {
                        return false;
                    }
                }
            }
            return true;
        } else {
            // For scalars or differing types, perform a strict comparison.
            return $expected === $actual;
        }
    }
}
