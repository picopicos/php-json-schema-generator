<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Exception;

use RuntimeException;
use Throwable;

class UnsupportedTypeException extends RuntimeException
{
    public function __construct(string $typeName, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('Type "%s" is not supported yet.', $typeName), $code, $previous);
    }
}
