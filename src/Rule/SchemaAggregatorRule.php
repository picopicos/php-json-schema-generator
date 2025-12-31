<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Rule;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PhpStanJsonSchema\Collector\PropertyCollector;
use PhpStanJsonSchema\Schema\ObjectSchema;
use PhpStanJsonSchema\Schema\RawSchema;
use PhpStanJsonSchema\Schema\SchemaMetadata;
use PhpStanJsonSchema\Writer\SchemaWriter;

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
        /** @var array<string, list<array{className: class-string, propertyName: string, schema: array<string, mixed>}>> $collectedData */
        $collectedData = $node->get(PropertyCollector::class);

        /** @var array<class-string, array<string, array{className: class-string, propertyName: string, schema: array<string, mixed>}>> $groupedByClass */
        $groupedByClass = [];

        foreach ($collectedData as $properties) {
            foreach ($properties as $propertyData) {
                $groupedByClass[$propertyData['className']][$propertyData['propertyName']] = $propertyData;
            }
        }

        foreach ($groupedByClass as $className => $properties) {
            $schemaProperties = [];
            $required = [];

            foreach ($properties as $propertyName => $propertyData) {
                $schemaProperties[$propertyName] = new RawSchema($propertyData['schema']);
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
