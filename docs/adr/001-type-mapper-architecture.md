# 001. TypeMapper Architecture & Unknown Type Handling

Date: 2025-12-31
Status: Accepted

## Context
We need to convert PHPStan's `Type` objects (Rich Type System) into our Domain Schema Objects (JSON Schema).
Initially, this logic was hardcoded inside `PropertyCollector`. To scale the library and support various types (Primitive, Object, Array, Union, etc.), we need a flexible and extensible architecture.

Also, we need a strategy for handling types that are not yet supported or cannot be mapped to JSON Schema (e.g., `resource`). Implicitly falling back to `mixed` (Any Type) can lead to silent bugs or inaccurate schemas.

## Decision

### 1. Chain of Responsibility Pattern for Type Mapping
We will implement a `TypeMapper` system using the **Chain of Responsibility** (implemented via a Registry/Composite).

- **`TypeMapper` Interface**: Defines the contract `map(Type $type, Scope $scope): Option<Schema>`.
- **`TypeMapperRegistry`**: A composite mapper that holds a list of specialized mappers (`IntegerTypeMapper`, `StringTypeMapper`, etc.). It iterates through them and returns the result of the first mapper that supports the type.

### 2. Strict Unknown Type Handling (Default)
By default, if no mapper matches a given `Type`, the system will **throw an Exception** (`UnsupportedTypeException`).

- **Reasoning**:
    - **Safety**: Prevents "silent failures" where a complex type is accidentally serialized as `{}` (Any Type).
    - **Clarity**: Helps developers (us) identify missing mappers during development (TDD).
    - **Future-proof**: We can introduce a "Fallback Mode" (Warning/Mixed) later via configuration, but starting strict is safer.

### 3. Explicit "Mixed" vs "Unknown"
- **Mixed Type (`mixed`)**: Will be handled by an explicit `MixedTypeMapper` (generating `{}`).
- **Unknown Type**: A type that is *not* `mixed` but has no corresponding Mapper registered. This triggers the Exception.

## Consequences

### Positive
- **Extensibility**: Adding support for a new type (e.g., `Uuid`) only requires creating a new Mapper and registering it in `extension.neon`.
- **Testability**: Each Mapper can be unit-tested in isolation.
- **Robustness**: The strict exception policy ensures we don't generate invalid schemas for unsupported types.

### Negative
- **Initial Fragility**: Until we implement mappers for common types (String, Array, Object), the tool will crash frequently on real-world code. This is acceptable for the alpha stage.
- **Complexity**: Requires dependency injection configuration for the Registry.

## References
- [ARCHITECTURE.md](../ARCHITECTURE.md)
