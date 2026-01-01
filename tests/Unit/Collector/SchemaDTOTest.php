<?php

declare(strict_types=1);

namespace Tests\Unit\Collector;

use InvalidArgumentException;
use PhpStanJsonSchema\Controller\SchemaDTO;
use PhpStanJsonSchema\Schema\IntegerSchema;
use PhpStanJsonSchema\Schema\SchemaMetadata;
use PHPUnit\Framework\TestCase;
use stdClass;

class SchemaDTOTest extends TestCase
{
    public function testItRestoresObjectFromArray(): void
    {
        $schema = new IntegerSchema(new SchemaMetadata());
        $data = [
            'class_name' => stdClass::class,
            'serialized_schema' => base64_encode(serialize($schema)),
        ];

        $dto = SchemaDTO::fromArray($data);

        $this->assertSame(stdClass::class, $dto->className);
        $this->assertInstanceOf(IntegerSchema::class, $dto->schema);
    }

    public function testItSerializesObjectToArray(): void
    {
        $schema = new IntegerSchema(new SchemaMetadata());
        $dto = new SchemaDTO(stdClass::class, $schema);

        $array = $dto->toArray();

        $this->assertSame(stdClass::class, $array['class_name']);
        $this->assertEquals($schema, unserialize(base64_decode($array['serialized_schema'])));
    }

    public function testItThrowsExceptionIfDataIsNotArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Data must be an array');
        SchemaDTO::fromArray('invalid');
    }

    public function testItThrowsExceptionIfSchemaIsNotString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing or invalid "serialized_schema"');
        SchemaDTO::fromArray([
            'class_name' => stdClass::class,
            'serialized_schema' => ['some' => 'array'], // Not serialized string
        ]);
    }

    public function testItThrowsExceptionIfUnserializedObjectIsNotSchema(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unserialized schema is not an instance of Schema interface');

        $data = [
            'class_name' => stdClass::class,
            'serialized_schema' => base64_encode(serialize(new stdClass())),
        ];

        SchemaDTO::fromArray($data);
    }
}
