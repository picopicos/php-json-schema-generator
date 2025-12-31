<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Schema;

/**
 * A schema that is already serialized into an array.
 * Useful for passing data through PHPStan collectors.
 */
final readonly class RawSchema implements Schema
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private array $data
    ) {}

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
