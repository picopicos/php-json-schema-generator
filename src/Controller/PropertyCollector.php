<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Controller;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\ClassPropertyNode;
use PhpStanJsonSchema\Exception\UnsupportedTypeException;
use PhpStanJsonSchema\Mapper\TypeMapperRegistry;

/**
 * @implements Collector<ClassPropertyNode, PropertyDTO>
 */
class PropertyCollector implements Collector
{
    public function __construct(
        private readonly TypeMapperRegistry $typeMapper
    ) {}

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

        try {
            $schema = $this->typeMapper->map($resolvedType);
        } catch (UnsupportedTypeException) {
            // TODO: Implement warning/error reporting based on user configuration
            // Skip unsupported types for now to avoid crashing analysis
            return null;
        }

        return new PropertyDTO(
            className: $classReflection->getName(),
            propertyName: $propertyName,
            schema: $schema
        );
    }
}
