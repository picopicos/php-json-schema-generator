<?php

declare(strict_types=1);

namespace PhpJsonSchemaGenerator\Domain\Schema;

final readonly class StringSchema implements Schema
{
    public function __construct(
        public SchemaMetadata $metadata,
        public ?int $minLength = null,
        public ?int $maxLength = null,
        public ?string $pattern = null,
        public ?string $format = null,
        public ?string $default = null,
        /** @var list<string>|null */
        public ?array $enum = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_merge(
            $this->metadata->jsonSerialize(),
            array_filter([
                'type' => 'string',
                'minLength' => $this->minLength,
                'maxLength' => $this->maxLength,
                'pattern' => $this->pattern,
                'format' => $this->format,
                'default' => $this->default,
                'enum' => $this->enum,
            ], fn ($value) => $value !== null)
        );
    }
}
