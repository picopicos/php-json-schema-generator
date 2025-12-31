## Purpose
This PR implements the end-to-end pipeline from PHPStan analysis to JSON Schema file output, incorporating a major architectural shift to **Class-Based Collection** for better cohesion and performance.

## Architectural Changes (ADR-002)
- **Shift to `InClassNode`**: Instead of collecting scattered properties, we now process the entire class at once using `SchemaCollector`.
- **`ClassSchemaBuilder`**: Introduced a dedicated service to iterate over class properties and resolve types using the Registry, producing a complete `ObjectSchema`.
- **Simplified Aggregation**: `SchemaAggregatorRule` no longer handles complex grouping logic; it simply receives the fully formed `ObjectSchema` and writes it to disk.
- **Serialization Strategy**: To robustly handle object passing across PHPStan's parallel processes, `Schema` objects are explicitly serialized/unserialized at the boundary, preserving the object graph.

## Implementation Details
- **`SchemaCollector`**: Replaces `PropertyCollector`. Collects `ObjectSchema` per class.
- **`SchemaDTO`**: Replaces `PropertyDTO`. Handles strict validation and deserialization of the schema data in the Rule.
- **`SchemaWriter`**: Writes the generated schemas to `.json` files.
- **`extension.neon`**: Updated to register the new services.

## Verification
- **Unit Tests**: Added `ObjectSchemaTest` and `SchemaDTOTest` to verify domain logic and serialization safety.
- **Integration Tests**: 
    - `SchemaExportTest` verifies file generation and content correctness.
    - Added cases for:
        - `RangeDto` (Integer range)
        - `MultipleDto` (Multiple properties)
        - `UnsupportedDto` (Graceful handling of unsupported types like string)
- **Static Analysis**: All checks passed.
