<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Controller;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\InClassNode;
use PhpStanJsonSchema\Mapper\ClassSchemaBuilderInterface;

/**
 * @phpstan-type schema_data array{
 *     class_name: class-string,
 *     schema: array<string, mixed>
 * }
 * @implements Collector<InClassNode, schema_data>
 */
class SchemaCollector implements Collector
{
    public function __construct(
        private readonly ClassSchemaBuilderInterface $schemaBuilder
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
        $classReflection = $node->getClassReflection();

        // Skip anonymous classes or internal classes if necessary
        if ($classReflection->isAnonymous()) {
            return null;
        }

        $schema = $this->schemaBuilder->build($classReflection, $scope);

        return [
            'class_name' => $classReflection->getName(),
            'schema' => $schema->jsonSerialize(),
        ];
    }
}
