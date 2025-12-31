# Schema Unit Tests

This directory contains unit tests for individual Schema classes (e.g., `IntegerSchema`, `StringSchema`).
These tests focus on verifying:

1.  **Serialization**: The PHP object correctly serializes to the expected JSON structure.
2.  **Constraint Validation**: The Schema class enforces logical constraints (e.g., `min <= max`) upon instantiation.
3.  **Constraint Consistency**: The Schema class enforces consistency between related constraints (e.g., `default` must be in `enum`, `enum` values must be within `min/max`).
4.  **Schema Validity**: The generated JSON is a valid JSON Schema (verified using `opis/json-schema`).