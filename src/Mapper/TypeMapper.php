<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Mapper;

use PHPStan\Type\Type;
use PhpStanJsonSchema\Schema\Schema;

interface TypeMapper
{
    public function map(Type $type): ?Schema;
}
