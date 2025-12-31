<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Schema;

use JsonSerializable;

interface Schema extends JsonSerializable
{
    /**
     * @phpstan-return array<string, mixed>
     */
    public function jsonSerialize(): array;
}
