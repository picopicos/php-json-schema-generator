# Roadmap

This document outlines the development milestones for the PHPStan-First JSON Schema Generator.

## Milestone 0.1.0: Architectural Foundation (MVP)

**Goal**: Establish the 3-Stage Pipeline architecture and prove the concept with minimal types.
**Focus**: Correctly implement the flow: `PHPStan Node` -> `Collector` -> `Mapper` -> `Schema DTO` -> `JSON`.

### Scope
- **Supported Types**: `int`, `string`, `bool` (and their `nullable` variants).
- **Supported Features**:
    - `required` detection based on default values.
    - Basic `ObjectSchema` generation.
- **Excluded (Deferred to 0.2.0)**:
    - Arrays / Lists.
    - Unions (complex ones like `int|string`).
    - Generics.
    - Integer Ranges.
    - Nested Objects.

### Tasks
#### 1. Intermediate Representation (IR)
- [ ] Define `SchemaInterface`.
- [ ] Implement `ObjectSchema` (Support `addProperty` and `required` management).
- [ ] Implement `IntegerSchema`, `StringSchema`, `BooleanSchema`, `NullSchema`.

#### 2. Mapper System
- [ ] Define `TypeMapperInterface`.
- [ ] Implement `IntegerMapper`, `StringMapper`, `BooleanMapper`.
- [ ] Implement `MixedMapper` (Fallback for testing).
- [ ] Implement `CompositeTypeMapper` (The main dispatcher).

#### 3. Collector & Rule
- [ ] Implement `PropertyCollector` logic to detect `default value` status.
- [ ] Update `SchemaAggregatorRule` to orchestrate the pipeline.

#### 4. Verification
- [ ] **End-to-End Test**:
    - Input: `class SimpleDto { public int $id; public ?string $name = null; }`
    - Expected Output: Valid JSON Schema with correct `required` fields.

---

## Future Milestones (Draft)

### Milestone 0.2.0: Complex Types
- Support `array` and `list<T>`.
- Support `union` types (`oneOf`, `anyOf`).
- Support nested objects (References or Inline).

### Milestone 0.3.0: Advanced Constraints
- Integer Ranges (`min`, `max`).
- String Patterns (`regex`).
- Array Shapes (`array{key: val}`).

### Milestone 1.0.0: First Stable Release
- Full OpenAPI 3.1 Compatibility.
- Comprehensive Documentation.
- Performance Tuning.
