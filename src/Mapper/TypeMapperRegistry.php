<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Mapper;

use PHPStan\Type\Type;
use PhpStanJsonSchema\Exception\UnsupportedTypeException;
use PhpStanJsonSchema\Schema\Schema;

final readonly class TypeMapperRegistry implements TypeMapper
{
    /**
     * @param iterable<TypeMapper> $mappers
     */
    public function __construct(
        private iterable $mappers
    ) {}

    /**
     * @throws UnsupportedTypeException
     */
    public function map(Type $type): Schema
    {
        foreach ($this->mappers as $mapper) {
            $schema = $mapper->map($type);
            if ($schema !== null) {
                return $schema;
            }
        }

        throw new UnsupportedTypeException($type->describe(\PHPStan\Type\VerbosityLevel::typeOnly()));
    }
}
