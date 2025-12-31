<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Traits\JsonSchemaAssertions;

class SchemaExportTest extends TestCase
{
    use JsonSchemaAssertions;

    private string $outputDir;

    protected function setUp(): void
    {
        $this->outputDir = sys_get_temp_dir() . '/phpstan-json-schema-test-' . uniqid();
        if (!mkdir($this->outputDir, 0o777, true) && !is_dir($this->outputDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $this->outputDir));
        }
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->outputDir);
    }

    public function testExportsIntegerRange(): void
    {
        $configFile = $this->createTestConfig();
        $fixtureFile = __DIR__ . '/../Fixtures/Integer/RangeDto.php';
        $phpstanBin = __DIR__ . '/../../vendor/bin/phpstan';

        // Run PHPStan analysis on the fixture
        $cmd = sprintf(
            '%s analyse -c %s %s --no-progress',
            $phpstanBin,
            $configFile,
            $fixtureFile
        );

        exec($cmd, $output, $resultCode);
        
        $expectedFile = $this->outputDir . '/Tests.Fixtures.Integer.RangeDto.json';
        $this->assertFileExists($expectedFile, 'Schema file was not generated: ' . implode("\n", $output));

        $json = (string) file_get_contents($expectedFile);
        /** @var array{type: string, properties: array<string, array{type: string, minimum?: int, maximum?: int}>, required: list<string>} $data */
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        // Validate structure
        $this->assertValidJsonSchema($json);

        // Validate content
        $this->assertSame('object', $data['type']);
        
        $this->assertProperty($data, 'rating', 'integer');
        
        $rating = $data['properties']['rating'];
        $this->assertSame(1, $rating['minimum'] ?? null);
        $this->assertSame(10, $rating['maximum'] ?? null);

        $this->assertContains('rating', $data['required']);
    }

    public function testExportsMultipleProperties(): void
    {
        $configFile = $this->createTestConfig();
        $fixtureFile = __DIR__ . '/../Fixtures/Object/MultipleDto.php';
        $phpstanBin = __DIR__ . '/../../vendor/bin/phpstan';

        $cmd = sprintf('%s analyse -c %s %s --no-progress', $phpstanBin, $configFile, $fixtureFile);
        exec($cmd, $output, $resultCode);

        $expectedFile = $this->outputDir . '/Tests.Fixtures.Object.MultipleDto.json';
        $this->assertFileExists($expectedFile);

        $json = (string) file_get_contents($expectedFile);
        /** @var array{type: string, properties: array<string, array{type: string}>, required: list<string>} $data */
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertValidJsonSchema($json);
        $this->assertProperty($data, 'id', 'integer');
        $this->assertProperty($data, 'age', 'integer');
        
        $this->assertContains('id', $data['required']);
        $this->assertContains('age', $data['required']);
    }

    public function testSkipsUnsupportedTypes(): void
    {
        $configFile = $this->createTestConfig();
        $fixtureFile = __DIR__ . '/../Fixtures/Unsupported/UnsupportedDto.php';
        $phpstanBin = __DIR__ . '/../../vendor/bin/phpstan';

        $cmd = sprintf('%s analyse -c %s %s --no-progress', $phpstanBin, $configFile, $fixtureFile);
        exec($cmd, $output, $resultCode);

        $expectedFile = $this->outputDir . '/Tests.Fixtures.Unsupported.UnsupportedDto.json';
        $this->assertFileExists($expectedFile);

        $json = (string) file_get_contents($expectedFile);
        /** @var array{type: string, properties: array<string, array{type: string}>, required: list<string>} $data */
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertValidJsonSchema($json);
        $this->assertProperty($data, 'id', 'integer');
        $this->assertArrayNotHasKey('name', $data['properties'], 'Unsupported type (string) should be skipped');
        
        $this->assertContains('id', $data['required']);
        $this->assertNotContains('name', $data['required']);
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function assertProperty(array $schema, string $propertyName, string $expectedType): void
    {
        $this->assertArrayHasKey('properties', $schema);
        $properties = $schema['properties'];
        assert(is_array($properties));
        
        $this->assertArrayHasKey($propertyName, $properties);
        $property = $properties[$propertyName];
        assert(is_array($property));
        $this->assertSame($expectedType, $property['type']);
    }

    private function createTestConfig(): string
    {
        $configPath = $this->outputDir . '/test-config.neon';
        $extensionPath = realpath(__DIR__ . '/../../extension.neon');
        $content = <<<NEON
            includes:
            	- phar://phpstan.phar/conf/bleedingEdge.neon
            	- {$extensionPath}

            parameters:
            	level: max
            	phpstanJsonSchema:
            		outputDirectory: {$this->outputDir}
            NEON;
        file_put_contents($configPath, $content);

        return $configPath;
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