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
        $fixtureFile = __DIR__ . '/../Fixtures/RangeDto.php';
        $phpstanBin = __DIR__ . '/../../vendor/bin/phpstan';

        // Run PHPStan analysis on the fixture
        $cmd = sprintf(
            '%s analyse -c %s %s --no-progress',
            $phpstanBin,
            $configFile,
            $fixtureFile
        );

        exec($cmd, $output, $resultCode);



        $expectedFile = $this->outputDir . '/Tests.Fixtures.RangeDto.json';

        $this->assertFileExists($expectedFile, 'Schema file was not generated: ' . implode("\n", $output));

        $json = (string) file_get_contents($expectedFile);
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        assert(is_array($data));

        // Validate structure
        $this->assertValidJsonSchema($json);

        // Validate content
        $this->assertSame('object', $data['type']);
        $this->assertArrayHasKey('properties', $data);
        assert(is_array($data['properties']));
        $this->assertArrayHasKey('rating', $data['properties']);

        $rating = $data['properties']['rating'];
        assert(is_array($rating));
        $this->assertSame('integer', $rating['type']);
        $this->assertSame(1, $rating['minimum']);
        $this->assertSame(10, $rating['maximum']);

        assert(is_iterable($data['required'] ?? []));
        $this->assertContains('rating', $data['required'] ?? []);
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
