<?php

declare(strict_types=1);

namespace Tests\Unit\Mapper;

use PHPStan\Type\StringType;
use PhpStanJsonSchema\Exception\UnsupportedTypeException;
use PhpStanJsonSchema\Mapper\TypeMapper;
use PhpStanJsonSchema\Mapper\TypeMapperRegistry;
use PhpStanJsonSchema\Schema\Schema;
use PHPUnit\Framework\TestCase;

class TypeMapperRegistryTest extends TestCase
{
    public function testItReturnsFirstMatchingSchema(): void
    {
        $schema = $this->createMock(Schema::class);

        $mapper1 = $this->createMock(TypeMapper::class);
        $mapper1->method('map')->willReturn(null);

        $mapper2 = $this->createMock(TypeMapper::class);
        $mapper2->method('map')->willReturn($schema);

        $registry = new TypeMapperRegistry([$mapper1, $mapper2]);

        $result = $registry->map(new StringType());
        $this->assertSame($schema, $result);
    }

    public function testItThrowsExceptionIfNoMapperMatches(): void
    {
        $mapper = $this->createMock(TypeMapper::class);
        $mapper->method('map')->willReturn(null);

        $registry = new TypeMapperRegistry([$mapper]);

        $this->expectException(UnsupportedTypeException::class);
        $this->expectExceptionMessage('Type "string" is not supported yet.');

        $registry->map(new StringType());
    }
}
