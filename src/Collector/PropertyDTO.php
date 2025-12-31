<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Collector;

use PhpStanJsonSchema\Schema\Schema;

/**
 * @phpstan-type property_dto_json array{
 *     class: class-string,
 *     property: string,
 *     schema: array<string, mixed>
 * }
 */
final readonly class PropertyDTO
{
    /**
     * @param class-string $className
     */
    public function __construct(
        public string $className,
        public string $propertyName,
        public Schema $schema,
    ) {}
}
