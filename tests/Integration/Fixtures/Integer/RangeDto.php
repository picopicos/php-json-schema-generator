<?php

declare(strict_types=1);

namespace Tests\Integration\Fixtures\Integer;

final class RangeDto
{
    /**
     * @param int<1, 10> $rating
     */
    public function __construct(
        public int $rating
    ) {}
}
