<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\Attributes\DataProvider;
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

    /**
     * @param class-string $className
     */
    #[DataProvider('provideFixtures')]
    public function testSchemaGeneration(string $phpFile, string $jsonFile, string $className): void
    {
        $configFile = $this->createTestConfig();
        $phpstanBin = __DIR__ . '/../../vendor/bin/phpstan';

        $cmd = sprintf(
            '%s analyse -c %s %s --no-progress',
            $phpstanBin,
            $configFile,
            $phpFile
        );

        exec($cmd, $output, $resultCode);

        $expectedFilename = str_replace('\\', '.', $className) . '.json';
        $actualFile = $this->outputDir . '/' . $expectedFilename;

        $this->assertFileExists($actualFile, 'Schema file was not generated: ' . implode("\n", $output));

        $actualJson = (string) file_get_contents($actualFile);
        $expectedJson = (string) file_get_contents($jsonFile);

        $actualData = json_decode($actualJson, true, 512, JSON_THROW_ON_ERROR);
        $expectedData = json_decode($expectedJson, true, 512, JSON_THROW_ON_ERROR);

        // Verify JSON Schema validity
        $this->assertValidJsonSchema($actualJson);

        // Verify exact structure match
        $this->assertSame($expectedData, $actualData);
    }

    /**
     * @return iterable<string, array{phpFile: string, jsonFile: string, className: class-string}>
     */
    public static function provideFixtures(): iterable
    {
        $fixtureDir = __DIR__ . '/Fixtures';
        $files = glob($fixtureDir . '/*/*.php');

        if ($files === false) {
            return;
        }

        foreach ($files as $phpFile) {
            $jsonFile = str_replace('.php', '.json', $phpFile);
            if (!file_exists($jsonFile)) {
                continue;
            }

            // Derive class name from path: Integration/Fixtures/Integer/RangeDto.php
            // -> Tests\Integration\Fixtures\Integer\RangeDto
            $relativePath = str_replace([__DIR__ . '/Fixtures', '.php'], ['', ''], $phpFile);
            /** @var class-string $className */
            $className = 'Tests\\Integration\\Fixtures' . str_replace('/', '\\', $relativePath);

            yield $className => [
                'phpFile' => $phpFile,
                'jsonFile' => $jsonFile,
                'className' => $className,
            ];
        }
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
