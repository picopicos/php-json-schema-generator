<?php

declare(strict_types=1);

namespace PhpJsonSchemaGenerator\Collector;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\ClassPropertyNode;
use PHPStan\Type\IntegerRangeType;
use PHPStan\Type\VerbosityLevel;

/**
 * @implements Collector<ClassPropertyNode, array<string, mixed>>
 */
class PropertyCollector implements Collector
{
    public function getNodeType(): string
    {
        return ClassPropertyNode::class;
    }

    public function processNode(Node $node, Scope $scope)
    {
        if (!$scope->isInClass()) {
            return null;
        }

        $classReflection = $scope->getClassReflection();
        $propertyName = $node->getName();

        $propertyReflection = $classReflection->getProperty($propertyName, $scope);
        $resolvedType = $propertyReflection->getReadableType();

        $data = [
            'class' => $classReflection->getName(),
            'property' => $propertyName,
            'type' => $resolvedType->describe(VerbosityLevel::precise()),
        ];

        // Specific handling for IntegerRangeType (E2E target)
        if ($resolvedType instanceof IntegerRangeType) {
            $data['min'] = $resolvedType->getMin();
            $data['max'] = $resolvedType->getMax();
        }

        return $data;
    }
}
