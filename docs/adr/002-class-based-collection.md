# 002. Class-Based Schema Collection

Date: 2025-12-31
Status: Accepted

## Context
Initially, the `PropertyCollector` collected metadata for each property individually using `ClassPropertyNode`. The `SchemaAggregatorRule` then had to group these scattered properties by class to reconstruct the object structure.

This approach had several downsides:
- **Unnatural Data Flow**: Splitting a class into properties only to regroup them later is inefficient.
- **Context Loss**: Individual property nodes lack easy access to class-level context (e.g., class attributes, inheritance hierarchy) without extra lookups.
- **Complex Aggregation**: The Aggregator Rule became responsible for logic (grouping/merging) that properly belongs to the domain analysis phase.

## Decision

### 1. Shift to Class-Level Collection (`InClassNode`)
We will switch the collector target from `ClassPropertyNode` to `InClassNode`.
The Collector will now process the entire class at once, generating a complete `ObjectSchema` for the class.

### 2. Introduce `ClassSchemaBuilder`
We introduce a `ClassSchemaBuilder` service responsible for converting a `ClassReflection` into an `ObjectSchema`.
- It iterates over the class's properties.
- It delegates type resolution to `TypeMapperRegistry`.
- It constructs the `ObjectSchema`.

### 3. Simplified Aggregation Rule
The `SchemaAggregatorRule` becomes a dumb pipe. It receives fully formed `ObjectSchema` objects from the Collector and passes them to the `SchemaWriter`.

## Consequences

### Positive
- **Cohesion**: Schema generation logic is centralized in `ClassSchemaBuilder`, not split between Collector and Rule.
- **Simplicity**: The Aggregator Rule is significantly simplified.
- **Performance**: Reduced overhead of passing thousands of small property DTOs through the PHPStan result cache.

### Negative
- **Memory**: Passing larger `ObjectSchema` objects might slightly increase memory usage per node, but this is offset by the reduction in total node count.

## References
- [001. TypeMapper Architecture & Unknown Type Handling](001-type-mapper-architecture.md)
