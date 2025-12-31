<?php

declare(strict_types=1);

namespace Tests\Unit\Schema;

use PhpStanJsonSchema\Exception\InvalidSchemaConstraintException;
use PhpStanJsonSchema\Schema\IntegerSchema;
use PhpStanJsonSchema\Schema\SchemaMetadata;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Traits\JsonSchemaAssertions;

/**
 * @phpstan-import-type schema_metadata_json from SchemaMetadata
 * @phpstan-import-type integer_schema_json from IntegerSchema
 * @phpstan-type integer_args array{
 *     metadata: SchemaMetadata,
 *     minimum?: int|null,
 *     maximum?: int|null,
 *     default?: int|null,
 *     enum?: list<int>|null
 * }
 */
class IntegerSchemaTest extends TestCase
{
    use JsonSchemaAssertions;

    /**
     * @phpstan-param integer_args $constructorArgs
     * @phpstan-param integer_schema_json $expectedJson
     */
    #[DataProvider('provideValidConfigurations')]
    public function testItSerializesToValidJsonSchema(array $constructorArgs, array $expectedJson): void
    {
        $schema = new IntegerSchema(
            metadata: $constructorArgs['metadata'],
            minimum: $constructorArgs['minimum'] ?? null,
            maximum: $constructorArgs['maximum'] ?? null,
            default: $constructorArgs['default'] ?? null,
            enum: $constructorArgs['enum'] ?? null
        );

        $json = json_encode($schema, JSON_THROW_ON_ERROR);
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        assert(is_array($data));

        // Check structure
        $this->assertSame($expectedJson, $data);

        // Functional Checks
        $this->assertValidJsonSchema($json);

        // Basic value check if default is present
        if (isset($expectedJson['default'])) {
            $this->assertSchemaAccepts($json, $expectedJson['default']);
        }
    }

    /**
     * @phpstan-return iterable<string, array{constructorArgs: integer_args, expectedJson: integer_schema_json}>
     */
    public static function provideValidConfigurations(): iterable
    {
        $metadata = new SchemaMetadata(title: 'Age', description: 'User age');

        yield 'full_configuration' => [
            'constructorArgs' => [
                'metadata' => $metadata,
                'minimum' => 0,
                'maximum' => 150,
                'default' => 20,
            ],
            'expectedJson' => [
                'title' => 'Age',
                'description' => 'User age',
                'type' => 'integer',
                'minimum' => 0,
                'maximum' => 150,
                'default' => 20,
            ],
        ];

        yield 'minimal_configuration' => [
            'constructorArgs' => [
                'metadata' => new SchemaMetadata(),
            ],
            'expectedJson' => [
                'type' => 'integer',
            ],
        ];

        yield 'boundary_min_equals_max' => [
            'constructorArgs' => [
                'metadata' => new SchemaMetadata(),
                'minimum' => 10,
                'maximum' => 10,
            ],
            'expectedJson' => [
                'type' => 'integer',
                'minimum' => 10,
                'maximum' => 10,
            ],
        ];

        yield 'boundary_default_equals_min' => [
            'constructorArgs' => [
                'metadata' => new SchemaMetadata(),
                'minimum' => 5,
                'default' => 5,
            ],
            'expectedJson' => [
                'type' => 'integer',
                'minimum' => 5,
                'default' => 5,
            ],
        ];

        yield 'boundary_default_equals_max' => [
            'constructorArgs' => [
                'metadata' => new SchemaMetadata(),
                'maximum' => 5,
                'default' => 5,
            ],
            'expectedJson' => [
                'type' => 'integer',
                'maximum' => 5,
                'default' => 5,
            ],
        ];

        yield 'enum_values' => [
            'constructorArgs' => [
                'metadata' => new SchemaMetadata(),
                'enum' => [1, 2, 3],
            ],
            'expectedJson' => [
                'type' => 'integer',
                'enum' => [1, 2, 3],
            ],
        ];
    }

    /**
     * @phpstan-param integer_args $constructorArgs
     */
    #[DataProvider('provideInvalidConfigurations')]
    public function testItThrowsExceptionForInvalidConfiguration(array $constructorArgs, string $expectedMessage): void
    {
        $this->expectException(InvalidSchemaConstraintException::class);
        $this->expectExceptionMessage($expectedMessage);

        new IntegerSchema(
            metadata: $constructorArgs['metadata'],
            minimum: $constructorArgs['minimum'] ?? null,
            maximum: $constructorArgs['maximum'] ?? null,
            default: $constructorArgs['default'] ?? null,
            enum: $constructorArgs['enum'] ?? null
        );
    }

    /**
     * @phpstan-return iterable<string, array{constructorArgs: integer_args, expectedMessage: string}>
     */
    public static function provideInvalidConfigurations(): iterable
    {
        $meta = new SchemaMetadata();

        yield 'min_greater_than_max' => [
            'constructorArgs' => ['metadata' => $meta, 'minimum' => 11, 'maximum' => 10],
            'expectedMessage' => 'Invalid schema constraint "minimum": Min cannot be greater than Max',
        ];

        yield 'default_less_than_min' => [
            'constructorArgs' => ['metadata' => $meta, 'minimum' => 10, 'default' => 9],
            'expectedMessage' => 'Invalid schema constraint "default": Default value is lower than minimum',
        ];

        yield 'default_greater_than_max' => [
            'constructorArgs' => ['metadata' => $meta, 'maximum' => 10, 'default' => 11],
            'expectedMessage' => 'Invalid schema constraint "default": Default value is greater than maximum',
        ];

        yield 'default_not_in_enum' => [
            'constructorArgs' => ['metadata' => $meta, 'default' => 5, 'enum' => [1, 2, 3]],
            'expectedMessage' => 'Invalid schema constraint "default": Default value must be one of the enum values',
        ];

        yield 'enum_under_min' => [
            'constructorArgs' => ['metadata' => $meta, 'minimum' => 5, 'enum' => [4, 5, 6]],
            'expectedMessage' => 'Invalid schema constraint "enum": Enum value is lower than minimum',
        ];

        yield 'enum_over_max' => [
            'constructorArgs' => ['metadata' => $meta, 'maximum' => 5, 'enum' => [4, 5, 6]],
            'expectedMessage' => 'Invalid schema constraint "enum": Enum value is greater than maximum',
        ];
    }
}
