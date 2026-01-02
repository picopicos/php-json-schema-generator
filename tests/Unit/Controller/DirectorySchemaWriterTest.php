<?php

declare(strict_types=1);

namespace Tests\Unit\Controller;

use PhpStanJsonSchema\Controller\DirectorySchemaWriter;
use PhpStanJsonSchema\Schema\IntegerSchema;
use PhpStanJsonSchema\Schema\SchemaMetadata;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

class DirectorySchemaWriterTest extends TestCase
{
    private string $outputDir;

    protected function setUp(): void
    {
        $this->outputDir = sys_get_temp_dir() . '/schema-writer-test-' . uniqid();
    }

    protected function tearDown(): void
    {
        if (is_dir($this->outputDir)) {
            $this->removeDirectory($this->outputDir);
        }
    }

    public function testItWritesSchemaToFile(): void
    {
        $writer = new DirectorySchemaWriter($this->outputDir);
        $schema = new IntegerSchema(new SchemaMetadata());

        $writer->write(stdClass::class, $schema->jsonSerialize());

        $expectedPath = $this->outputDir . '/stdClass.json';
        $this->assertFileExists($expectedPath);
    }

    public function testItThrowsExceptionForInvalidClassName(): void
    {
        $writer = new DirectorySchemaWriter($this->outputDir);
        $schema = new IntegerSchema(new SchemaMetadata());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid class name for file generation');

        /** @phpstan-ignore argument.type */
        $writer->write('App/../Secret', $schema->jsonSerialize());
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff((array) scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->removeDirectory("$dir/$file") : unlink("$dir/$file");
        }
        rmdir($dir);
    }
}
