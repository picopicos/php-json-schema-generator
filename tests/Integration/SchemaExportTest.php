<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

class SchemaExportTest extends TestCase
{
    public function testExportsIntegerRange(): void
    {
        // Run PHPStan analysis on the fixture
        $cmd = sprintf(
            '%s/../../vendor/bin/phpstan clear-result-cache -c %s/../phpstan.neon && %s/../../vendor/bin/phpstan analyse -c %s/../phpstan.neon %s/../Fixtures/RangeDto.php --error-format=json --no-progress',
            __DIR__,
            __DIR__,
            __DIR__,
            __DIR__,
            __DIR__
        );

        $output = shell_exec($cmd);
        $this->assertIsString($output, 'shell_exec returned null or false');

        /** @var array{files: array<string, array{messages: list<array{message: string, line: int}>}>} $json */
        $json = json_decode($output, true);

        $found = false;
        foreach ($json['files'] as $file => $errors) {
            foreach ($errors['messages'] as $error) {
                if (str_starts_with($error['message'], 'SCHEMA_EXPORT:')) {
                    /** @var array{className: string, propertyName: string, schema: array{minimum: int, maximum: int}} $data */
                    $data = json_decode(substr($error['message'], 14), true);
                    if ($data['propertyName'] === 'rating') {
                        $this->assertArrayHasKey('minimum', $data['schema']);
                        $this->assertArrayHasKey('maximum', $data['schema']);

                        $this->assertEquals(1, $data['schema']['minimum']);
                        $this->assertEquals(10, $data['schema']['maximum']);
                        $found = true;
                    }
                }
            }
        }

        $this->assertTrue($found, 'Could not find exported schema data for RangeDto::$rating. Output: ' . $output);
    }
}
