<?php

declare(strict_types=1);

namespace PhpJsonSchemaGenerator\Domain\Schema;

final readonly class ObjectSchema implements Schema
{
    /**
     * @param array<string, Schema> $properties
     * @param list<string>|null $required
     */
    public function __construct(
        public SchemaMetadata $metadata,
        public array $properties,
        public ?array $required = null,
        public ?bool $additionalProperties = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_merge(
            $this->metadata->jsonSerialize(),
            array_filter([
                'type' => 'object',
                'properties' => $this->properties,
                'required' => $this->required,
                'additionalProperties' => $this->additionalProperties,
            ], fn ($value) => $value !== null)
        );
    }
}
