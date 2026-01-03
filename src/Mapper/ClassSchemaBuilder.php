<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Mapper;

use InvalidArgumentException;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PhpStanJsonSchema\Schema\ObjectSchema;
use PhpStanJsonSchema\Schema\SchemaMetadata;

final readonly class ClassSchemaBuilder implements ClassSchemaBuilderInterface
{
    public function __construct(
        private TypeMapper $typeMapper
    ) {}

    public function build(ClassReflection $classReflection, Scope $scope): ObjectSchema
    {
        if ($classReflection->isAnonymous()) {
            throw new InvalidArgumentException('Anonymous classes are not supported for schema generation.');
        }

        $properties = [];
        $required = [];

        // Use native reflection to iterate over properties to ensure we only capture
        // physical properties actually defined in the class code (ignoring @property tags).
        // PHPStan's getProperties() might include virtual properties (@property tags)
        // which we don't want to support implicitly without explicit handling logic.
        foreach ($classReflection->getNativeReflection()->getProperties() as $nativeProperty) {
            $propertyName = $nativeProperty->getName();

            if (!$classReflection->hasProperty($propertyName)) {
                continue;
            }

            $propertyReflection = $classReflection->getProperty($propertyName, $scope);

            if (!$propertyReflection->isPublic()) {
                continue;
            }

            $type = $propertyReflection->getReadableType();

            $schema = $this->typeMapper->map($type);
            if ($schema !== null) {
                $properties[$propertyName] = $schema;

                $isOptional = false;
                if ($nativeProperty->hasDefaultValue()) {
                    $isOptional = true;
                } elseif ($nativeProperty->isPromoted()) {
                    $constructor = $classReflection->getNativeReflection()->getConstructor();
                    if ($constructor !== null) {
                        foreach ($constructor->getParameters() as $parameter) {
                            if ($parameter->getName() === $propertyName) {
                                if ($parameter->isDefaultValueAvailable()) {
                                    $isOptional = true;
                                }
                                break;
                            }
                        }
                    }
                }

                if (!$isOptional) {
                    $required[] = $propertyName;
                }
            }
        }

        return new ObjectSchema(
            metadata: new SchemaMetadata(),
            properties: $properties,
            required: $required
        );
    }
}
