<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Controller;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;

/**
 * @phpstan-import-type schema_data from SchemaCollector
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
        /** @var array<string, list<schema_data>> $collectedData */
        $collectedData = $node->get(SchemaCollector::class);

        foreach ($collectedData as $fileSchemas) {
            foreach ($fileSchemas as $schemaData) {
                $this->schemaWriter->write($schemaData['class_name'], $schemaData['schema']);
            }
        }

        return [];
    }
}
