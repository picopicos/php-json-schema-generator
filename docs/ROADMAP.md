# Roadmap

This document outlines the development milestones for the PHPStan-First JSON Schema Generator.

## Milestone 0.1.0: Architectural Foundation (MVP)

**Goal**: Establish the 3-Stage Pipeline architecture and prove the concept with minimal types.
**Output**: Self-contained JSON Schema files for simple DTOs.

### Core Features
- **Pipeline**: `Collector` (Required detection) -> `Mapper` (Type conversion) -> `Schema DTO` (IR).
- **Registry**: Basic implementation of `SchemaRegistry` to support `Ref by Default`.
- **Types**: `int`, `string`, `bool`, `null`, and simple `User-Defined Classes`.
- **References**: Recursion handling for nested objects (e.g., `User` <-> `Group`).
- **Naming**: Use PHP property names as-is.

### Tasks
#### 1. Intermediate Representation (IR)
- [ ] Define `SchemaInterface`.
- [ ] Implement `ObjectSchema`, `ReferenceSchema`, `PrimitiveSchema`.

#### 2. Mapper System (Chain of Responsibility)
- [ ] Define `TypeMapperInterface`.
- [ ] Implement `PrimitiveMapper` (Int, String, Bool).
- [ ] Implement `RefMapper` (Delegates to Registry).
- [ ] Implement `MixedMapper` (Fallback to Any).

#### 3. Registry & Collector
- [ ] Implement `SchemaRegistry` (Track processing types, store definitions).
- [ ] Implement `PropertyCollector` (Default value detection).

---

## Future Milestones

### Milestone 0.2.0: Complex Types & Collections
- Support `array` and `list<T>`.
- Support `array{key: val}` (Shapes).
- Support `DateTimeInterface` (via `DateMapper` -> `format: date-time`).
- Support `UnionType` (`int|string` -> `anyOf`).

### Milestone 0.3.0: Constraints & Customization
- Integer Ranges (`int<min, max>`).
- String Patterns (`regex`).
- **Naming Strategy**: Support `snake_case` conversion option.
- **Attributes**: Support `#[JsonProperty]`, `#[Title]`, `#[Description]`.

### Milestone 1.0.0: First Stable Release
- Full OpenAPI 3.1 Compatibility.
- Component Library Mode (Export all schemas in one file).
- Production-ready error handling and logging.