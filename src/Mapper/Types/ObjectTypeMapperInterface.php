<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Mapper\Types;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PhpStanJsonSchema\Schema\ObjectSchema;

interface ObjectTypeMapperInterface
{
    public function build(ClassReflection $classReflection, Scope $scope): ObjectSchema;
}
