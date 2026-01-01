<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Controller;

use InvalidArgumentException;
use PhpStanJsonSchema\Schema\Schema;

/**
 * Data Transfer Object to transport Schema objects between Collector and Rule.
 *
 * This DTO handles the serialization of the Schema object to ensure it can be
 * safely passed through PHPStan's collection mechanism (which requires return values
 * to be serializable/cacheable arrays).
 *
 * @phpstan-type schema_data array{
 *     class_name: class-string,
 *     serialized_schema: string
 * }
 */
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
     * @return schema_data
     */
    public function toArray(): array
    {
        return [
            'class_name' => $this->className,
            'serialized_schema' => base64_encode(serialize($this->schema)),
        ];
    }

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

        $className = $data['class_name'] ?? null;
        if (!is_string($className) || $className === '') {
            throw new InvalidArgumentException('Missing or invalid "class_name".');
        }

        if (!isset($data['serialized_schema']) || !is_string($data['serialized_schema'])) {
            throw new InvalidArgumentException('Missing or invalid "serialized_schema". Expected serialized string.');
        }

        $serializedSchema = base64_decode($data['serialized_schema'], true);
        if ($serializedSchema === false) {
            throw new InvalidArgumentException('Invalid base64 encoded schema.');
        }

        $schema = unserialize($serializedSchema);
        if (!$schema instanceof Schema) {
            throw new InvalidArgumentException('Unserialized schema is not an instance of Schema interface.');
        }

        // Narrowing to class-string
        if (!class_exists($className) && !interface_exists($className) && !trait_exists($className)) {
            throw new InvalidArgumentException(sprintf('Class "%s" does not exist.', $className));
        }

        return new self(
            className: $className,
            schema: $schema,
        );
    }
}
