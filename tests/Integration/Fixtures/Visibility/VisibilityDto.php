<?php

declare(strict_types=1);

namespace Tests\Integration\Fixtures\Visibility;

final readonly class VisibilityDto
{
    public function __construct(
        public int $publicProperty,
        protected int $protectedProperty,
        private int $privateProperty,
    ) {}
}