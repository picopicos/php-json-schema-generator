<?php

declare(strict_types=1);

namespace Tests\Unit\Collector;

use InvalidArgumentException;
use PhpStanJsonSchema\Collector\SchemaDTO;
use PhpStanJsonSchema\Schema\IntegerSchema;
use PhpStanJsonSchema\Schema\SchemaMetadata;
use PHPUnit\Framework\TestCase;

class SchemaDTOTest extends TestCase
{
    public function testItRestoresObjectFromArray(): void
    {
        $schema = new IntegerSchema(new SchemaMetadata());
        $data = [
            'className' => \stdClass::class,
            'schema' => serialize($schema),
        ];

        $dto = SchemaDTO::fromArray($data);

        $this->assertSame(\stdClass::class, $dto->className);
        $this->assertInstanceOf(IntegerSchema::class, $dto->schema);
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
        $this->expectExceptionMessage('Missing or invalid "schema"');
        SchemaDTO::fromArray([
            'className' => \stdClass::class,
            'schema' => ['some' => 'array'], // Not serialized string
        ]);
    }

    public function testItThrowsExceptionIfUnserializedObjectIsNotSchema(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unserialized schema is not an instance of Schema interface');
        
        $data = [
            'className' => \stdClass::class,
            'schema' => serialize(new \stdClass()),
        ];

        SchemaDTO::fromArray($data);
    }
}
