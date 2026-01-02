<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Controller;

interface SchemaWriter
{
    /**
     * @param class-string $className
     * @param array<string, mixed> $schema
     */
    public function write(string $className, array $schema): void;
}
