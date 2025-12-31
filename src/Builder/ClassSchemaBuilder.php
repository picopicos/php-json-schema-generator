<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Builder;

use PhpStanJsonSchema\Exception\UnsupportedTypeException;
use PhpStanJsonSchema\Mapper\TypeMapperRegistry;
use PhpStanJsonSchema\Schema\ObjectSchema;
use PhpStanJsonSchema\Schema\SchemaMetadata;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;

final readonly class ClassSchemaBuilder
{
    public function __construct(
        private TypeMapperRegistry $typeMapper
    ) {}

    public function build(ClassReflection $classReflection, Scope $scope): ObjectSchema
    {
        $properties = [];
        $required = [];
        
        // Use native reflection to iterate over properties, then upgrade to PHPStan reflection for types
        // This ensures we capture all properties relevant to the class structure
        foreach ($classReflection->getNativeReflection()->getProperties() as $nativeProperty) {
            $propertyName = $nativeProperty->getName();
            
            if (!$classReflection->hasProperty($propertyName)) {
                continue;
            }
            
            $propertyReflection = $classReflection->getProperty($propertyName, $scope);
            $type = $propertyReflection->getReadableType();

            try {
                $schema = $this->typeMapper->map($type);
                $properties[$propertyName] = $schema;
                $required[] = $propertyName; // Default to required for now
            } catch (UnsupportedTypeException) {
                // TODO: Handle unsupported types (warning/log)
                continue;
            }
        }

        return new ObjectSchema(
            metadata: new SchemaMetadata(),
            properties: $properties,
            required: $required
        );
    }
}