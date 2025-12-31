<?php

declare(strict_types=1);

namespace Tests\Integration\Fixtures\Object;

final class MultipleDto
{
    /**
     * @param int<1, 100> $age
     */
    public function __construct(
        public int $id,
        public int $age
    ) {}
}
