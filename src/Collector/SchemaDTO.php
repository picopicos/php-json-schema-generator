<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Collector;

use InvalidArgumentException;
use PhpStanJsonSchema\Schema\Schema;

final readonly class SchemaDTO
{
    /**
     * @param class-string $className
     */
    public function __construct(
        public string $className,
        public Schema $schema,
    ) {}

    /**
     * @param mixed $data
     * @return self
     * @throws InvalidArgumentException
     */
    public static function fromArray(mixed $data): self
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array.');
        }

        if (!isset($data['schema']) || !is_string($data['schema'])) {
            throw new InvalidArgumentException('Missing or invalid "schema". Expected serialized string.');
        }

        $className = $data['className'] ?? null;
        if (!is_string($className) || $className === '') {
            throw new InvalidArgumentException('Missing or invalid "className".');
        }

        $schema = unserialize($data['schema']);
        if (!$schema instanceof Schema) {
            throw new InvalidArgumentException('Unserialized schema is not an instance of Schema interface.');
        }

        // Narrowing to class-string
        assert(class_exists($className) || interface_exists($className) || trait_exists($className));

        return new self(
            className: $className,
            schema: $schema,
        );
    }
}
