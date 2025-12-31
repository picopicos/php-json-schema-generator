<?php

declare(strict_types=1);

namespace PhpJsonSchemaGenerator\Domain\Schema;

final readonly class RefSchema implements Schema
{
    public function __construct(
        public string $ref,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return [
            '$ref' => $this->ref,
        ];
    }
}
