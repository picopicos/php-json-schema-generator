<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Schema;

use LogicException;
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
    /**
     * @param list<int>|null $enum
     * @throws InvalidSchemaConstraintException
     */
    public function __construct(
        public SchemaMetadata $metadata,
        public ?int $minimum = null,
        public ?int $maximum = null,
        public ?int $default = null,
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

        if ($this->enum !== null) {
            if ($this->default !== null && !in_array($this->default, $this->enum, true)) {
                throw new InvalidSchemaConstraintException(
                    'default',
                    'Default value must be one of the enum values',
                    ['default' => $this->default, 'enum' => $this->enum]
                );
            }

            foreach ($this->enum as $value) {
                if ($this->minimum !== null && $value < $this->minimum) {
                    throw new InvalidSchemaConstraintException(
                        'enum',
                        'Enum value is lower than minimum',
                        ['value' => $value, 'minimum' => $this->minimum]
                    );
                }
                if ($this->maximum !== null && $value > $this->maximum) {
                    throw new InvalidSchemaConstraintException(
                        'enum',
                        'Enum value is greater than maximum',
                        ['value' => $value, 'maximum' => $this->maximum]
                    );
                }
            }
        }
    }

    /**
     * @phpstan-return integer_schema_json
     * @throws LogicException
     */
    public function jsonSerialize(): array
    {
        $metadata = $this->metadata->jsonSerialize();
        $schema = array_filter([
            'type' => 'integer',
            'minimum' => $this->minimum,
            'maximum' => $this->maximum,
            'default' => $this->default,
            'enum' => $this->enum,
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
