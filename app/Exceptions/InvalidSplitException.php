<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when expense splits don't sum to the total amount.
 */
class InvalidSplitException extends Exception
{
    /**
     * Create a new InvalidSplitException.
     *
     * @param string $message
     * @param string $expected
     * @param string $actual
     */
    public function __construct(
        string $message = 'Invalid expense split',
        public readonly string $expected = '0.00',
        public readonly string $actual = '0.00',
    ) {
        parent::__construct($message);
    }

    /**
     * Create exception for splits sum mismatch.
     *
     * @param string $expected Total expense amount
     * @param string $actual Sum of owed_shares
     * @return self
     */
    public static function splitsSumMismatch(string $expected, string $actual): self
    {
        return new self(
            message: "Sum of splits ({$actual}) does not equal expense total ({$expected})",
            expected: $expected,
            actual: $actual,
        );
    }
}
