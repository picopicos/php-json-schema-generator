<?php

declare(strict_types=1);

namespace Tests\Fixtures;

final class UnsupportedDto
{
    public function __construct(
        public int $id,
        public string $name // String is not supported yet
    ) {}
}
