<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Schema;

use PhpStanJsonSchema\Exception\InvalidSchemaConstraintException;

/**
 * @phpstan-import-type schema_metadata_json from SchemaMetadata
 * @phpstan-type integer_schema_json array{
 *     type: 'integer',
 *     minimum?: int,
 *     maximum?: int,
 *     default?: int,
 *     enum?: list<int>
 * } & schema_metadata_json
 */
final readonly class IntegerSchema implements Schema
{
    public function __construct(
        public SchemaMetadata $metadata,
        public ?int $minimum = null,
        public ?int $maximum = null,
        public ?int $default = null,
        /** @var list<int>|null */
        public ?array $enum = null,
    ) {
        if ($this->minimum !== null && $this->maximum !== null && $this->minimum > $this->maximum) {
            throw new InvalidSchemaConstraintException(
                'minimum',
                'Min cannot be greater than Max',
                ['minimum' => $this->minimum, 'maximum' => $this->maximum]
            );
        }

        if ($this->default !== null) {
            if ($this->minimum !== null && $this->default < $this->minimum) {
                throw new InvalidSchemaConstraintException(
                    'default',
                    'Default value is lower than minimum',
                    ['default' => $this->default, 'minimum' => $this->minimum]
                );
            }
            if ($this->maximum !== null && $this->default > $this->maximum) {
                throw new InvalidSchemaConstraintException(
                    'default',
                    'Default value is greater than maximum',
                    ['default' => $this->default, 'maximum' => $this->maximum]
                );
            }
        }
    }

    /**
     * @phpstan-return integer_schema_json
     */
    public function jsonSerialize(): array
    {
        return array_merge(
            $this->metadata->jsonSerialize(),
            array_filter([
                'type' => 'integer',
                'minimum' => $this->minimum,
                'maximum' => $this->maximum,
                'default' => $this->default,
                'enum' => $this->enum,
            ], fn($value) => $value !== null)
        );
    }
}
