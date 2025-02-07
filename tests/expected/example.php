<?php

define('PI', 3.14159); // Mathematical constant for circle calculations.

define('DEFAULT_GREETING', "Hello, World!"); // Default greeting message.

/**
 * Calculate the sum of two numbers.
 * @param number $a The first operand.
 * @param number $b The second operand.
 * @return number
 */
function calculateSum(number $a, number $b): number {
    // Use basic arithmetic to return the sum.
    // Validate that both parameters are numbers and handle any necessary error checking.
    // TODO: Implement function body
}

/**
 * Generate a personalized greeting message.
 * @param string $name The name to include in the greeting.
 * @return string
 */
function generateGreeting(string $name): string {
    // If the provided name is empty, use the DEFAULT_GREETING constant.
    // Ensure the output string is properly formatted.
    // TODO: Implement function body
}

/**
 * A class that performs basic arithmetic operations.
 */
class Calculator {
    /** @var number Stores the result of the last operation performed. */
    private number $lastResult = 0;

    /**
     * Add two numbers and update lastResult.
     * @param number $a
     * @param number $b
     * @return number
     */
    function add(number $a, number $b): number {
        // Use the calculateSum function to compute the sum.
        // Update the lastResult property with the new value.
        // TODO: Implement function body
    }

    /**
     * Reset the calculator to its initial state.
     * @return void
     */
    function reset(): void {
        // Set the lastResult property back to 0.
        // TODO: Implement function body
    }
}
