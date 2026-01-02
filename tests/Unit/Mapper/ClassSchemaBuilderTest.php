<?php

declare(strict_types=1);

namespace Tests\Unit\Mapper;

use PHPStan\Testing\PHPStanTestCase;
use PhpStanJsonSchema\Mapper\ClassSchemaBuilder;
use PhpStanJsonSchema\Mapper\TypeMapper;
use PhpStanJsonSchema\Schema\IntegerSchema;
use PhpStanJsonSchema\Schema\ObjectSchema;
use Tests\Integration\Fixtures\Integer\RangeDto;

class ClassSchemaBuilderTest extends PHPStanTestCase
{
    private ClassSchemaBuilder $builder;

    protected function setUp(): void
    {
        $typeMapper = $this->createMock(TypeMapper::class);
        $typeMapper->method('map')->willReturn(new IntegerSchema(new \PhpStanJsonSchema\Schema\SchemaMetadata()));

        $this->builder = new ClassSchemaBuilder($typeMapper);
    }

    public function testItBuildsSchemaFromRealClassReflection(): void
    {
        $reflectionProvider = $this->createReflectionProvider();
        $classReflection = $reflectionProvider->getClass(RangeDto::class);
        $scope = $this->createMock(\PHPStan\Analyser\Scope::class);

        $objectSchema = $this->builder->build($classReflection, $scope);


        $this->assertInstanceOf(ObjectSchema::class, $objectSchema);
        $this->assertArrayHasKey('rating', $objectSchema->properties);
        $this->assertContains('rating', $objectSchema->required);
    }
}
