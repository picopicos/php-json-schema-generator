<?php

declare(strict_types=1);

namespace Tests\Unit\Schema;

use LogicException;
use PhpStanJsonSchema\Schema\IntegerSchema;
use PhpStanJsonSchema\Schema\ObjectSchema;
use PhpStanJsonSchema\Schema\SchemaMetadata;
use PHPUnit\Framework\TestCase;
use Tests\Traits\JsonSchemaAssertions;

class ObjectSchemaTest extends TestCase
{
    use JsonSchemaAssertions;

    public function testItSerializesBasicObject(): void
    {
        $schema = new ObjectSchema(
            metadata: new SchemaMetadata(title: 'User', description: 'User DTO'),
            properties: [
                'id' => new IntegerSchema(new SchemaMetadata()),
            ],
            required: ['id'],
            additionalProperties: false
        );

        $json = json_encode($schema, JSON_THROW_ON_ERROR);
        $data = json_decode($json, true);

        $this->assertValidJsonSchema($json);
        
        $this->assertSame('object', $data['type']);
        $this->assertSame('User', $data['title']);
        $this->assertSame('User DTO', $data['description']);
        $this->assertSame(['id'], $data['required']);
        $this->assertFalse($data['additionalProperties']);
        
        $this->assertArrayHasKey('id', $data['properties']);
        $this->assertSame('integer', $data['properties']['id']['type']);
    }

    public function testItOmitsRequiredIfEmpty(): void
    {
        $schema = new ObjectSchema(new SchemaMetadata());
        $json = json_encode($schema, JSON_THROW_ON_ERROR);
        $data = json_decode($json, true);

        $this->assertArrayNotHasKey('required', $data);
    }
}
