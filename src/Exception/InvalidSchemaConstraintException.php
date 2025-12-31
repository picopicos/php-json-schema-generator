<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Exception;

use InvalidArgumentException;

final class InvalidSchemaConstraintException extends InvalidArgumentException
{
    /**
     * @param string $constraint The name of the constraint that was violated (e.g., 'minimum', 'default').
     * @param string $reason The reason why the value is invalid.
     * @param array<string, mixed> $context Additional context values for rich error reporting.
     */
    public function __construct(
        public readonly string $constraint,
        string $reason,
        public readonly array $context = []
    ) {
        parent::__construct(sprintf('Invalid schema constraint "%s": %s', $constraint, $reason));
    }
}
