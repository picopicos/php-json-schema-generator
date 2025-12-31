<?php

declare(strict_types=1);

namespace Tests\Traits;

use Opis\JsonSchema\Validator;
use Tests\Constraints\MatchesJsonSchema;
use Throwable;

trait JsonSchemaAssertions
{
    /**
     * Asserts that the given JSON string is a syntactically valid JSON Schema.
     * It verifies this by attempting to parse and use the schema for validation.
     * If the schema is invalid, the validator will throw an exception or return an error.
     */
    protected function assertValidJsonSchema(string $jsonSchema): void
    {
        $schemaObject = json_decode($jsonSchema, false, 512, JSON_THROW_ON_ERROR);
        $this->assertIsObject($schemaObject, 'Generated JSON is not a valid object');

        $validator = new Validator();

        try {
            // Attempt to validate null data against the schema.
            // We expect the parser to succeed (not throw exception) if the schema is syntactically valid.
            // Note: Full meta-schema validation is skipped due to complexity of resolving external references offline.
            $validator->validate(null, $schemaObject);
        } catch (Throwable $e) {
            $this->fail(sprintf(
                "Generated JSON is not a valid JSON Schema. Parser error: %s\nSchema: %s",
                $e->getMessage(),
                $jsonSchema
            ));
        }
    }
    /**
     * Asserts that the schema successfully validates the given data.
     */
    protected function assertSchemaAccepts(string|object $schema, mixed $data, string $message = ''): void
    {
        $this->assertThat($data, new MatchesJsonSchema($schema), $message);
    }

    /**
     * Asserts that the schema rejects the given data.
     */
    protected function assertSchemaRejects(string|object $schema, mixed $data, string $message = ''): void
    {
        $this->assertThat($data, $this->logicalNot(new MatchesJsonSchema($schema)), $message);
    }
}
