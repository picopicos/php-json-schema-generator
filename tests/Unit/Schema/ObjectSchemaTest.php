<?php

declare(strict_types=1);

namespace Tests\Unit\Schema;

use PhpStanJsonSchema\Schema\IntegerSchema;
use PhpStanJsonSchema\Schema\ObjectSchema;
use PhpStanJsonSchema\Schema\SchemaMetadata;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Traits\JsonSchemaAssertions;

/**
 * @phpstan-import-type object_schema_json from ObjectSchema
 * @phpstan-type object_args array{
 *     metadata: SchemaMetadata,
 *     properties?: array<string, \PhpStanJsonSchema\Schema\Schema>,
 *     required?: list<string>,
 *     additionalProperties?: bool
 * }
 */
class ObjectSchemaTest extends TestCase
{
    use JsonSchemaAssertions;

    /**
     * @phpstan-param object_args $constructorArgs
     * @phpstan-param object_schema_json $expectedJson
     */
    #[DataProvider('provideValidConfigurations')]
    public function testItSerializesToValidJsonSchema(array $constructorArgs, array $expectedJson): void
    {
        $schema = new ObjectSchema(
            metadata: $constructorArgs['metadata'],
            properties: $constructorArgs['properties'] ?? [],
            required: $constructorArgs['required'] ?? [],
            additionalProperties: $constructorArgs['additionalProperties'] ?? false,
        );

        $json = json_encode($schema, JSON_THROW_ON_ERROR);
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        assert(is_array($data));

        $this->assertValidJsonSchema($json);
        $this->assertSame($expectedJson, $data);
    }

    /**
     * @phpstan-return iterable<string, array{constructorArgs: object_args, expectedJson: object_schema_json}>
     */
    public static function provideValidConfigurations(): iterable
    {
        yield 'basic_object' => [
            'constructorArgs' => [
                'metadata' => new SchemaMetadata(title: 'User', description: 'User DTO'),
                'properties' => [
                    'id' => new IntegerSchema(new SchemaMetadata()),
                ],
                'required' => ['id'],
                'additionalProperties' => false,
            ],
            'expectedJson' => [
                'title' => 'User',
                'description' => 'User DTO',
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                ],
                'required' => ['id'],
                'additionalProperties' => false,
            ],
        ];

        yield 'empty_properties' => [
            'constructorArgs' => [
                'metadata' => new SchemaMetadata(),
            ],
            'expectedJson' => [
                'type' => 'object',
                'additionalProperties' => false,
            ],
        ];

        yield 'with_additional_properties_true' => [
            'constructorArgs' => [
                'metadata' => new SchemaMetadata(),
                'additionalProperties' => true,
            ],
            'expectedJson' => [
                'type' => 'object',
                'additionalProperties' => true,
            ],
        ];
    }
}
