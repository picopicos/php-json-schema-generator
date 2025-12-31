<?php

declare(strict_types=1);

namespace PhpJsonSchemaGenerator\Domain\Schema;

use JsonSerializable;

final readonly class SchemaMetadata implements JsonSerializable
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?bool $deprecated = null,
        public ?bool $readOnly = null,
        public ?bool $writeOnly = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'title' => $this->title,
            'description' => $this->description,
            'deprecated' => $this->deprecated,
            'readOnly' => $this->readOnly,
            'writeOnly' => $this->writeOnly,
        ], fn ($value) => $value !== null);
    }
}
