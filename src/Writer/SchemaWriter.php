<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Writer;

use PhpStanJsonSchema\Schema\Schema;

interface SchemaWriter
{
    /**
     * @param class-string $className
     */
    public function write(string $className, Schema $schema): void;
}
