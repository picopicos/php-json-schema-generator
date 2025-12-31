<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Rule;

use InvalidArgumentException;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PhpStanJsonSchema\Collector\SchemaCollector;
use PhpStanJsonSchema\Collector\SchemaDTO;
use PhpStanJsonSchema\Writer\SchemaWriter;

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
                try {
                    $dto = SchemaDTO::fromArray($schemaData);
                    $this->schemaWriter->write($dto->className, $dto->schema);
                } catch (InvalidArgumentException $e) {
                    // Log or ignore invalid data? For now, we rely on types.
                    continue;
                }
            }
        }

        return [];
    }
}
