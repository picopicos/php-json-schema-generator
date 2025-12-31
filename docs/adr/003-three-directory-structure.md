# 003. Three-Directory Structure (Schema, Mapper, Controller)

Date: 2025-12-31
Status: Accepted

## Context
As the library grows, the horizontal splitting of directories by pattern names (`Builder`, `Collector`, `Rule`, `Writer`) becomes fragmented. Related logic is scattered, making it harder to navigate.

## Decision
We adopt a simplified **Three-Directory Structure** to group components by their fundamental purpose:

1.  **`src/Schema/` (Domain Model)**:
    - Pure data structures representing JSON Schema objects.
    - Implementation of the `Schema` interface.
2.  **`src/Mapper/` (Business Logic)**:
    - Logic for converting PHPStan types and reflections into `Schema` objects.
    - Includes what was previously known as "Builders" (e.g., `ObjectTypeMapper`).
3.  **`src/Controller/` (I/O & Orchestration)**:
    - Entry points and exit points.
    - PHPStan specific hooks (`Collector`, `Rule`).
    - External system interactions (`Writer`).
    - Internal orchestration DTOs.

## Consequences

### Positive
- **Cohesion**: All transformation logic is now under `Mapper`.
- **Clarity**: High-level separation between the Data Model, the Transformation Logic, and the Input/Output layers.
- **Scalability**: Adding new types only affects `Schema` and `Mapper`.

### Negative
- **Controller Size**: The `Controller` directory might hold varied components (both input and output), but at the current scale, this is manageable.

## References
- [002. Class-Based Schema Collection](002-class-based-collection.md)
