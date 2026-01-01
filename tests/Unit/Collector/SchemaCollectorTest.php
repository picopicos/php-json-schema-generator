<?php

declare(strict_types=1);

namespace Tests\Unit\Collector;

use PHPStan\Node\InClassNode;
use PHPStan\Testing\PHPStanTestCase;
use PhpStanJsonSchema\Controller\SchemaCollector;
use PhpStanJsonSchema\Mapper\ClassSchemaBuilderInterface;
use PhpStanJsonSchema\Schema\ObjectSchema;
use PhpStanJsonSchema\Schema\SchemaMetadata;
use Tests\Integration\Fixtures\Integer\RangeDto;

class SchemaCollectorTest extends PHPStanTestCase
{
    private SchemaCollector $collector;
    private ClassSchemaBuilderInterface&\PHPUnit\Framework\MockObject\MockObject $builder;

    protected function setUp(): void
    {
        $this->builder = $this->createMock(ClassSchemaBuilderInterface::class);
        $this->collector = new SchemaCollector($this->builder);
    }

    public function testItCollectsSchemaForClass(): void
    {
        $reflectionProvider = $this->createReflectionProvider();
        $classReflection = $reflectionProvider->getClass(RangeDto::class);

        // @phpstan-ignore phpstanApi.constructor
        $node = new InClassNode(new \PhpParser\Node\Stmt\Class_('RangeDto'), $classReflection);

        $scope = $this->createMock(\PHPStan\Analyser\Scope::class);

        $expectedSchema = new ObjectSchema(new SchemaMetadata());
        $this->builder->expects($this->once())
            ->method('build')
            ->with($classReflection, $scope)
            ->willReturn($expectedSchema);

        $result = $this->collector->processNode($node, $scope);

        $this->assertNotNull($result);
        $this->assertSame(RangeDto::class, $result['className']);
        $this->assertSame(base64_encode(serialize($expectedSchema)), $result['schema']);
    }
}
