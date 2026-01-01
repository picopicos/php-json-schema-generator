<?php

declare(strict_types=1);

namespace PhpStanJsonSchema\Controller;

use PhpStanJsonSchema\Schema\Schema;
use RuntimeException;

final readonly class DirectorySchemaWriter implements SchemaWriter
{
    public function __construct(
        private string $outputDirectory
    ) {}

    public function write(string $className, Schema $schema): void
    {
        $this->ensureDirectoryExists();

        // Sanitize class name for filename (App\Dto\User -> App.Dto.User.json)
        $filename = str_replace('\\', '.', ltrim($className, '\\')) . '.json';
        $path = $this->outputDirectory . DIRECTORY_SEPARATOR . $filename;

        $json = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        if (file_put_contents($path, $json) === false) {
            throw new RuntimeException(sprintf('Failed to write schema to "%s"', $path));
        }
    }

    private function ensureDirectoryExists(): void
    {
        if (!is_dir($this->outputDirectory) && !mkdir($this->outputDirectory, 0o777, true) && !is_dir($this->outputDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $this->outputDirectory));
        }
    }
}
