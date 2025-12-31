<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Collector;

use PhpParser\Node;
use PhpStanJsonSchema\Builder\ClassSchemaBuilder;
use PhpStanJsonSchema\Schema\Schema;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\InClassNode;

/**
 * @phpstan-type schema_data array{
 *     className: class-string,
 *     schema: string
 * }
 * @implements Collector<InClassNode, schema_data>
 */
class SchemaCollector implements Collector
{
    public function __construct(
        private readonly ClassSchemaBuilder $schemaBuilder
    ) {}

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @return schema_data|null
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        if (!$scope->isInClass()) {
            return null;
        }

        $classReflection = $node->getClassReflection();
        
        // Skip anonymous classes or internal classes if necessary
        if ($classReflection->isAnonymous()) {
            return null;
        }

        $schema = $this->schemaBuilder->build($classReflection, $scope);

        return [
            'className' => $classReflection->getName(),
            'schema' => serialize($schema),
        ];
    }
}
