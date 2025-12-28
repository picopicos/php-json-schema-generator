<?php

declare(strict_types=1);

namespace Tests;

use PhpJsonSchemaGenerator\SchemaGenerator;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Input\BasicDto;

final class SchemaGeneratorTest extends TestCase
{
    public function testGenerateBasicSchema(): void
    {
        $generator = new SchemaGenerator();
        $schema = $generator->generate(BasicDto::class);

        $expected = file_get_contents(__DIR__ . '/Fixtures/Expected/basic_dto.json');
        $this->assertIsString($expected);

        $this->assertJsonStringEqualsJsonString($expected, json_encode($schema, JSON_THROW_ON_ERROR));
    }
}
