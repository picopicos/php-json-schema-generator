<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Mapper;

use PHPStan\Type\IntegerRangeType;
use PHPStan\Type\Type;
use PhpStanJsonSchema\Schema\IntegerSchema;
use PhpStanJsonSchema\Schema\Schema;
use PhpStanJsonSchema\Schema\SchemaMetadata;

final readonly class IntegerTypeMapper implements TypeMapper
{
    public function map(Type $type): ?Schema
    {
        if ($type instanceof IntegerRangeType) {
            return new IntegerSchema(
                new SchemaMetadata(),
                minimum: $type->getMin(),
                maximum: $type->getMax()
            );
        }

        if ($type->isInteger()->yes()) {
            return new IntegerSchema(new SchemaMetadata());
        }

        return null;
    }
}
