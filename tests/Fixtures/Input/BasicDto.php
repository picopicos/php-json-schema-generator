<?php

declare(strict_types=1);

namespace Tests\Fixtures\Input;

final readonly class BasicDto
{
    /**
     * @param int<1, 10> $rating
     */
    public function __construct(
        public string $name,
        public int $age,
        public bool $isActive,
        public float $score,
        public int $rating,
    ) {
    }
}