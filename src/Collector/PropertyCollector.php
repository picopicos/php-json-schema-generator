<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Collector;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\ClassPropertyNode;
use PHPStan\Type\IntegerRangeType;
use PhpStanJsonSchema\Schema\IntegerSchema;
use PhpStanJsonSchema\Schema\SchemaMetadata;

/**
 * @implements Collector<ClassPropertyNode, PropertyDTO>
 */
class PropertyCollector implements Collector
{
    public function getNodeType(): string
    {
        return ClassPropertyNode::class;
    }

    public function processNode(Node $node, Scope $scope): ?PropertyDTO
    {
        if (!$scope->isInClass()) {
            return null;
        }

        $classReflection = $scope->getClassReflection();
        $propertyName = $node->getName();

        $propertyReflection = $classReflection->getProperty($propertyName, $scope);
        $resolvedType = $propertyReflection->getReadableType();

        // Temporary logic for POC (Will be moved to TypeMapper in Phase 2)
        $schema = new IntegerSchema(new SchemaMetadata());
        if ($resolvedType instanceof IntegerRangeType) {
            $schema = new IntegerSchema(
                new SchemaMetadata(),
                minimum: $resolvedType->getMin(),
                maximum: $resolvedType->getMax()
            );
        }

        return new PropertyDTO(
            className: $classReflection->getName(),
            propertyName: $propertyName,
            schema: $schema
        );
    }
}
