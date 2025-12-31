<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Builder;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PhpStanJsonSchema\Schema\ObjectSchema;

interface ClassSchemaBuilderInterface
{
    public function build(ClassReflection $classReflection, Scope $scope): ObjectSchema;
}
