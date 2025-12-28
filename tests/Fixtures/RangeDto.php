<?php

declare(strict_types=1);

namespace Tests\Fixtures;

final class RangeDto
{
    /**
     * @param int<1, 10> $rating
     */
    public function __construct(
        public int $rating
    ) {}
}
