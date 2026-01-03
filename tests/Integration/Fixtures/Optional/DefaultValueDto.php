<?php

declare(strict_types=1);

namespace Tests\Integration\Fixtures\Optional;

final readonly class DefaultValueDto
{
    public function __construct(
        public int $requiredInfo,
        public int $optionalInfo = 42
    ) {}
}
