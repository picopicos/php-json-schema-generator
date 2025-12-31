# Schema Testing Guidelines

When implementing a new `Schema` class, comprehensive unit tests are mandatory.
Follow these guidelines to ensure consistency and quality.

## Test Class Structure

Create a test class extending `PHPUnit\Framework\TestCase` and using the `JsonSchemaAssertions` trait.

```php
use PHPUnit\Framework\TestCase;
use Tests\Traits\JsonSchemaAssertions;

class MySchemaTest extends TestCase
{
    use JsonSchemaAssertions;
    // ...
}
```

## Mandatory Test Perspectives

### 1. JSON Serialization (Structure)
Verify that `jsonSerialize()` produces the exact array structure expected.
Use `assertSame($expected, $actual)` to compare the entire array at once.

### 2. Schema Validity (Syntax)
Verify that the generated JSON object is a valid JSON Schema parsable by standard validators.
Use `$this->assertValidJsonSchema($json)`.

### 3. Functional Validation (Behavior)
Verify that the generated schema correctly accepts valid data and rejects invalid data.
This ensures that constraints like `minimum`, `pattern`, or `required` are actually working.

- **Accepts**: `$this->assertSchemaAccepts($json, $validValue)`
- **Rejects**: `$this->assertSchemaRejects($json, $invalidValue)`

### 4. Constructor Validation (PHP Logic)
Verify that the Schema class itself throws `InvalidSchemaConstraintException` when instantiated with invalid constraints (e.g., `min > max`).

### 5. Boundary Values
Test strictly around the boundaries of constraints.
- `min`, `max`, `min - 1`, `max + 1`
- `minLength`, `maxLength`
- `minItems`, `maxItems`

## Example: IntegerSchemaTest

Use `#[DataProvider]` with **Named Arguments** to cover all scenarios efficiently.

```php
    /**
     * @phpstan-param my_schema_args $constructorArgs
     * @phpstan-param my_schema_json $expectedJson
     */
    #[DataProvider('provideValidConfigurations')]
    public function testItSerializesToValidJsonSchema(array $constructorArgs, array $expectedJson): void
    {
        $schema = new MySchema(
            // Use Named Arguments or extract manually for strict typing
            metadata: $constructorArgs['metadata'],
            // ...
        );

        $json = json_encode($schema, JSON_THROW_ON_ERROR);
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        assert(is_array($data));

        // 1. Structure
        $this->assertSame($expectedJson, $data);

        // 2. Syntax
        $this->assertValidJsonSchema($json);

        // 3. Behavior (if applicable)
        if (isset($expectedJson['default'])) {
            $this->assertSchemaAccepts($json, $expectedJson['default']);
        }
    }
```

## Checklist for New Schemas

- [ ] Does it implement `jsonSerialize()` with a strict PHPStan Array Shape?
- [ ] Are there tests for **Serialization** (structure)?
- [ ] Are there tests for **Validation Behavior** (accepts/rejects)?
- [ ] Are there tests for **Constructor Exceptions** (invalid constraints)?
- [ ] Are **Boundary Values** covered in DataProviders?
- [ ] Are **Named Arguments** used in DataProviders?
