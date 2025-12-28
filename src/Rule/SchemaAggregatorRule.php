<?php

declare(strict_types=1);

namespace PhpJsonSchemaGenerator\Rule;

use PhpJsonSchemaGenerator\Collector\PropertyCollector;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<CollectedDataNode>
 */
class SchemaAggregatorRule implements Rule
{
    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $collectedData = $node->get(PropertyCollector::class);
        $errors = [];

        foreach ($collectedData as $file => $properties) {
            foreach ($properties as $propertyData) {
                // For MVP: Dump collected data as a custom error message
                $json = json_encode($propertyData);
                $errors[] = RuleErrorBuilder::message("SCHEMA_EXPORT:$json")
                    ->file($file)
                    ->identifier('phpSchema.export')
                    ->build();
            }
        }

        return $errors;
    }
}
