<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Builder;

use PhpStanJsonSchema\Schema\ObjectSchema;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;

interface ClassSchemaBuilderInterface
{
    public function build(ClassReflection $classReflection, Scope $scope): ObjectSchema;
}
