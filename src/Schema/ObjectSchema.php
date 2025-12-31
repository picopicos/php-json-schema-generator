<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Schema;

use LogicException;

/**
 * @phpstan-import-type schema_metadata_json from SchemaMetadata
 * @phpstan-type object_schema_json array{
 *     type: 'object',
 *     properties?: array<string, array<string, mixed>>,
 *     required?: list<string>,
 *     additionalProperties: bool
 * } & schema_metadata_json
 */
final readonly class ObjectSchema implements Schema
{
    /**
     * @param array<string, Schema> $properties
     * @param list<string> $required
     */
    public function __construct(
        public SchemaMetadata $metadata,
        public array $properties = [],
        public array $required = [],
        public bool $additionalProperties = false,
    ) {}

    /**
     * @phpstan-return object_schema_json
     * @throws LogicException
     */
    public function jsonSerialize(): array
    {
        $metadata = $this->metadata->jsonSerialize();

        $properties = [];
        foreach ($this->properties as $name => $schema) {
            $properties[$name] = $schema->jsonSerialize();
        }

        $schema = array_filter([
            'type' => 'object',
            'properties' => $properties !== [] ? $properties : null,
            'required' => $this->required !== [] ? $this->required : null,
            'additionalProperties' => $this->additionalProperties,
        ], fn($value) => $value !== null);

        // @phpstan-ignore if.alwaysFalse
        if (array_intersect_key($metadata, $schema)) {
            throw new LogicException('Schema properties overlap with metadata');
        }

        return [
            ...$metadata,
            ...$schema,
        ];
    }
}
