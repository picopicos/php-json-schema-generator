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

        if (!isset($data['className']) || !is_string($data['className'])) {
            throw new InvalidArgumentException('Missing or invalid "className".');
        }

        if (!isset($data['propertyName']) || !is_string($data['propertyName'])) {
            throw new InvalidArgumentException('Missing or invalid "propertyName".');
        }

        if (!isset($data['schema']) || !is_array($data['schema'])) {
            throw new InvalidArgumentException('Missing or invalid "schema".');
        }

        /** @var class-string $className */
        $className = $data['className'];

        $schema = $data['schema'];
        // PHPStan cannot easily verify array keys are strings from mixed input without loop or explicit cast
        // For DTO purposes, we assume standard usage, but let's be safer
        // assert(array_is_list($schema) === false); // Too strict? JSON object is assoc array.

        return new self(
            className: $className,
            propertyName: $data['propertyName'],
            /** @var array<string, mixed> $schema */
            schema: $schema,
        );
    }
}
