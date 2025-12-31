# 001. TypeMapper Architecture & Unknown Type Handling

Date: 2025-12-31
Status: Accepted

## Context
We need to convert PHPStan's `Type` objects (Rich Type System) into our Domain Schema Objects (JSON Schema).
Initially, this logic was hardcoded inside `PropertyCollector`. To scale the library and support various types (Primitive, Object, Array, Union, etc.), we need a flexible and extensible architecture.

Also, we need a strategy for handling types that are not yet supported or cannot be mapped to JSON Schema (e.g., `resource`). Implicitly falling back to `mixed` (Any Type) can lead to silent bugs or inaccurate schemas.

A key challenge is determining **whether to prioritize static exhaustiveness (ensuring all types are handled at compile time) or extensibility (allowing users to add custom mappers).**

## Decision

### 1. Chain of Responsibility Pattern for Type Mapping
We will implement a `TypeMapper` system using the **Chain of Responsibility** (implemented via a Registry/Composite).

- **`TypeMapper` Interface**: Defines the contract `map(Type $type): ?Schema`.
- **`TypeMapperRegistry`**: A composite mapper that holds a list of specialized mappers (`IntegerTypeMapper`, `StringTypeMapper`, etc.). It iterates through them and returns the result of the first mapper that supports the type.

### 2. Prioritizing Extensibility over Exhaustiveness
We deliberately choose **Extensibility** over the static safety of a Visitor pattern.

*   **Why not Visitor Pattern?**
    *   While a Visitor would ensure every method (`visitInteger`, `visitString`) is implemented, it makes the system closed. Adding a new "semantic type" (e.g., handling `Ramsey\Uuid` specifically) would be difficult for 3rd-party users.
*   **Our Approach (Registry):**
    *   Allows users to plug in custom mappers via `extension.neon`.
    *   **Mitigation for Missing Types:** Instead of static checks, we rely on **Runtime Exceptions** (`UnsupportedTypeException`) to strictly identify coverage gaps.

### 3. Strict Unknown Type Handling (Default)
By default, if no mapper matches a given `Type`, the system will **throw an Exception** (`UnsupportedTypeException`).

- **Reasoning**:
    - **Safety**: Prevents "silent failures" where a complex type is accidentally serialized as `{}` (Any Type).
    - **Clarity**: Helps developers (us) identify missing mappers during development (TDD).

## Implementation & Usage Examples

### How it works
The `TypeMapperRegistry` is injected into the Collector. It holds a priority-ordered list of mappers.

```php
// src/Mapper/TypeMapperRegistry.php
public function map(Type $type): Schema
{
    foreach ($this->mappers as $mapper) {
        // First mapper to return a Schema wins
        $schema = $mapper->map($type);
        if ($schema !== null) {
            return $schema;
        }
    }
    // If no mapper matches, we fail fast
    throw new UnsupportedTypeException($type->describe(...));
}
```

### Extending the System (Sample)
To add support for a custom type (e.g., `Uuid`), a developer creates a new Mapper and registers it.

**1. Create Mapper:**
```php
class UuidTypeMapper implements TypeMapper
{
    public function map(Type $type): ?Schema
    {
        // Check if the type is Ramsey\Uuid\UuidInterface
        if ($type instanceof ObjectType && $type->getClassName() === 'Ramsey\Uuid\UuidInterface') {
            return new StringSchema(format: 'uuid');
        }
        return null;
    }
}
```

**2. Register in `extension.neon`:**
```yaml
services:
    -
        class: App\Mapper\UuidTypeMapper
        tags:
            - phpstan_json_schema.type_mapper
```

## Consequences

### Positive
- **Extensibility**: Adding support for a new type (e.g., `Uuid`) only requires creating a new Mapper and registering it in `extension.neon`.
- **Testability**: Each Mapper can be unit-tested in isolation.
- **Robustness**: The strict exception policy ensures we don't generate invalid schemas for unsupported types.

### Negative
- **Lack of Static Exhaustiveness**: We cannot guarantee at compile-time that all PHPStan types are handled. We must rely on comprehensive integration tests to catch "Unsupported Type" errors.
- **Initial Fragility**: Until we implement mappers for common types (String, Array, Object), the tool will crash frequently on real-world code.

## References
- [ARCHITECTURE.md](../ARCHITECTURE.md)