<?php

define('PI', 3.14159); // Mathematical constant for circle calculations.

define('DEFAULT_GREETING', "Hello, World!"); // Default greeting message.

/**
 * Calculate the sum of two ints.
 * @param int $a The first operand.
 * @param int $b The second operand.
 * @return int
 */
function calculateSum(
    int $a,
    int $b
): int
{
    // Use basic arithmetic to return the sum. Validate that both parameters are ints and handle any necessary error checking.
    // TODO: Implement function body
}

/**
 * Generate a personalized greeting message.
 * @param string $name The name to include in the greeting.
 * @return string
 */
function generateGreeting(
    string $name
): string
{
    // If the provided name is empty, use the DEFAULT_GREETING constant. Ensure the output string is properly formatted.
    // TODO: Implement function body
}

/**
 * A class that performs basic arithmetic operations.
 */
class Calculator
{
    /** @var int Stores the result of the last operation performed. */
    private int $lastResult = 0;

    /**
     * Add two ints and update lastResult.
     * @param int $a
     * @param int $b
     * @return int
     */
    function add(
        int $a,
        int $b
    ): int
    {
        // Use the calculateSum function to compute the sum. Update the lastResult property with the new value.
        // TODO: Implement function body
    }

    /**
     * Reset the calculator to its initial state.
     * @return void
     */
    function reset(): void
    {
        // Set the lastResult property back to 0.
        // TODO: Implement function body
    }
}
