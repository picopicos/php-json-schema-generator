# Architecture: PHPStan-First JSON Schema Generator

## 1. Core Concept

This library generates JSON Schema compatible with OpenAPI 3.1 directly from PHP code by leveraging **PHPStan's native type inference engine**.

### Key Principles
- **PHPStan Native**: We run as a PHPStan extension to ensure 100% compatibility with its type system.
- **No Runtime Reflection**: We strictly avoid PHP's native reflection (`new ReflectionClass`). All information is extracted via static analysis (AOT).
- **Intermediate Representation (IR)**: We convert PHPStan types into a strictly typed Schema Object Model (DTOs) before generating JSON. This decouples the analysis logic from the output format.

## 2. Architectural Pipeline

We adopt a **3-Stage Compiler Pipeline** to ensure scalability and loose coupling.

```mermaid
flowchart LR
    subgraph Phase1 [Phase 1: Analysis]
        A[PHPStan Engine] -->|Node| B(PropertyCollector)
    end
    
    subgraph Phase2 [Phase 2: Normalization]
        B -->|PHPStan Type| C{TypeMapper}
        C -->|Strategy| D[IntegerMapper]
        C -->|Strategy| E[StringMapper]
        C -->|Strategy| F[UnionMapper]
        D & E & F -->|Produces| G[**Schema DTO (IR)**]
    end
    
    subgraph Phase3 [Phase 3: Generation]
        G -->|Aggregated into| H[ObjectSchema]
        H -->|json_encode| I[JSON Schema File]
    end
```

### 2.1. Phase 1: Analysis (Collector)
- **Component**: `PropertyCollector`
- **Responsibility**: Inspects PHP Class AST and Reflection to determine **Property Metadata**.
- **Key Decision**: Determines `required` vs `optional` status.
    - **Rule**: If a property has no default value, it is `required`.
    - **Rule**: If a property has a default value, it is `optional`.

### 2.2. Phase 2: Normalization (Mapper)
- **Component**: `TypeMapper` (Composite Pattern)
- **Responsibility**: Converts `PHPStan\Type` into **Schema DTOs**.
- **Key Decision**: Determines the `type` and constraints (e.g., `minimum`, `pattern`).
    - **Note**: Mappers do NOT care about `required`. They only care about the value type (including `null`).
    - **Example**: `?int` is mapped to a Schema with `type: ["integer", "null"]`.

### 2.3. Phase 3: Generation (Schema DTOs)
- **Component**: `src/Schema/*` (e.g., `ObjectSchema`, `IntegerSchema`)
- **Responsibility**: Represents the JSON Schema structure in a strictly typed PHP Object Model.
- **Key Logic**:
    - `ObjectSchema` acts as the aggregate root.
    - It holds the list of properties (`properties`) and the list of required field names (`required`).
    - `jsonSerialize()` ensures the output matches the OpenAPI 3.1 / JSON Schema specification.

## 3. Mapping Strategy Examples

### 3.1. Required vs Nullable
We strictly separate the concept of "Required" (Key existence) and "Nullable" (Value type).

| PHP Definition | Schema Meaning | Responsibility | Generated Schema |
| :--- | :--- | :--- | :--- |
| `public int $a;` | **Required**, Not Null | Collector: Required<br>Mapper: Integer | `{"type": "integer"}`, `required: ["a"]` |
| `public ?int $a;` | **Required**, Nullable | Collector: Required<br>Mapper: Int \| Null | `{"type": ["integer", "null"]}`, `required: ["a"]` |
| `public int $a = 1;` | **Optional**, Not Null | Collector: Optional<br>Mapper: Integer | `{"type": "integer"}` |
| `public ?int $a = null;` | **Optional**, Nullable | Collector: Optional<br>Mapper: Int \| Null | `{"type": ["integer", "null"]}` |

## 4. Directory Structure

```text
src/
├── Collector/       # Phase 1: Extracts info from PHPStan
├── Mapper/          # Phase 2: Converts Types to Schema DTOs
├── Schema/          # Phase 3: Schema Object Model (The IR)
└── Rule/            # Entrypoint: PHPStan Rule to trigger the pipeline
```
