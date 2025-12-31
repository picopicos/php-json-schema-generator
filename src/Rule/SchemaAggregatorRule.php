<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Rule;

use PhpParser\Node;
use PhpStanJsonSchema\Collector\PropertyCollector;
use PhpStanJsonSchema\Collector\PropertyDTO;
use PhpStanJsonSchema\Schema\ObjectSchema;
use PhpStanJsonSchema\Schema\RawSchema;
use PhpStanJsonSchema\Schema\SchemaMetadata;
use PhpStanJsonSchema\Writer\SchemaWriter;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;

/**
 * @implements Rule<CollectedDataNode>
 */
class SchemaAggregatorRule implements Rule
{
    public function __construct(
        private readonly SchemaWriter $schemaWriter
    ) {}

    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $collectedData = $node->get(PropertyCollector::class);
        
        /** @var array<class-string, array<string, PropertyDTO>> $groupedByClass */
        $groupedByClass = [];

        foreach ($collectedData as $properties) {
            foreach ($properties as $propertyData) {
                $dto = PropertyDTO::fromArray($propertyData);
                $groupedByClass[$dto->className][$dto->propertyName] = $dto;
            }
        }

        foreach ($groupedByClass as $className => $properties) {
            $schemaProperties = [];
            $required = [];

            foreach ($properties as $propertyName => $dto) {
                $schemaProperties[$propertyName] = new RawSchema($dto->schema);
                // For now, all properties are required
                $required[] = $propertyName;
            }

            $objectSchema = new ObjectSchema(
                metadata: new SchemaMetadata(),
                properties: $schemaProperties,
                required: $required,
            );

            $this->schemaWriter->write($className, $objectSchema);
        }

        return [];
    }
}