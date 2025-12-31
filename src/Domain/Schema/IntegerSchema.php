<?php

declare(strict_types=1);

namespace PhpJsonSchemaGenerator\Domain\Schema;

use InvalidArgumentException;

final readonly class IntegerSchema implements Schema
{
    public function __construct(
        public SchemaMetadata $metadata,
        public ?int $minimum = null,
        public ?int $maximum = null,
        public ?int $default = null,
        /** @var list<int>|null */
        public ?array $enum = null,
    ) {
        if ($this->minimum !== null && $this->maximum !== null && $this->minimum > $this->maximum) {
            throw new InvalidArgumentException('Min cannot be greater than Max');
        }

        if ($this->default !== null) {
            if ($this->minimum !== null && $this->default < $this->minimum) {
                throw new InvalidArgumentException('Default value is lower than minimum');
            }
            if ($this->maximum !== null && $this->default > $this->maximum) {
                throw new InvalidArgumentException('Default value is greater than maximum');
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_merge(
            $this->metadata->jsonSerialize(),
            array_filter([
                'type' => 'integer',
                'minimum' => $this->minimum,
                'maximum' => $this->maximum,
                'default' => $this->default,
                'enum' => $this->enum,
            ], fn ($value) => $value !== null)
        );
    }
}
