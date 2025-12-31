<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Mapper\Types;

use PhpStanJsonSchema\Mapper\TypeMapper;
use PhpStanJsonSchema\Schema\IntegerSchema;
use PhpStanJsonSchema\Schema\Schema;
use PhpStanJsonSchema\Schema\SchemaMetadata;
use PHPStan\Type\IntegerRangeType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;

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
