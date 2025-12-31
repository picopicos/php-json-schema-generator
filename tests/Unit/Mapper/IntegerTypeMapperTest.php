<?php

declare(strict_types=1);

namespace Tests\Unit\Mapper;

use PhpStanJsonSchema\Mapper\Types\IntegerTypeMapper;
use PhpStanJsonSchema\Schema\IntegerSchema;
use PHPStan\Type\IntegerRangeType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPUnit\Framework\TestCase;

class IntegerTypeMapperTest extends TestCase
{
    private IntegerTypeMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new IntegerTypeMapper();
    }

    public function testItMapsIntegerType(): void
    {
        $type = new IntegerType();
        $schema = $this->mapper->map($type);

        $this->assertInstanceOf(IntegerSchema::class, $schema);
    }

    public function testItMapsIntegerRangeType(): void
    {
        // 1 to 10
        $type = IntegerRangeType::fromInterval(1, 10);
        $schema = $this->mapper->map($type);

        $this->assertInstanceOf(IntegerSchema::class, $schema);
        $this->assertSame(1, $schema->minimum);
        $this->assertSame(10, $schema->maximum);
    }

    public function testItReturnsNullForUnsupportedType(): void
    {
        $type = new StringType();
        $schema = $this->mapper->map($type);

        $this->assertNull($schema);
    }
}
