<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Collector;

use InvalidArgumentException;

final readonly class PropertyDTO
{
    /**
     * @param class-string $className
     * @param array<string, mixed> $schema
     */
    public function __construct(
        public string $className,
        public string $propertyName,
        public array $schema,
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

        $className = $data['className'] ?? null;
        $propertyName = $data['propertyName'] ?? null;
        $schemaData = $data['schema'] ?? null;

        if (!is_string($className) || $className === '') {
            throw new InvalidArgumentException('Missing or invalid "className".');
        }

        if (!is_string($propertyName)) {
            throw new InvalidArgumentException('Missing or invalid "propertyName".');
        }

        if (!is_array($schemaData)) {
            throw new InvalidArgumentException('Missing or invalid "schema".');
        }

        // Narrowing to class-string
        assert(class_exists($className) || interface_exists($className) || trait_exists($className));

        $schema = [];
        foreach ($schemaData as $key => $value) {
            assert(is_string($key));
            $schema[$key] = $value;
        }

        return new self(
            className: $className,
            propertyName: $propertyName,
            schema: $schema,
        );
    }
}
