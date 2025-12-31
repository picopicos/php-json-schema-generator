# Architecture: PHPStan-First JSON Schema Generator

## 1. Core Concept

This library generates JSON Schema compatible with OpenAPI 3.1 directly from PHP code by leveraging **PHPStan's native type inference engine**.

### Key Principles
- **PHPStan Native**: We run as a PHPStan extension to ensure 100% compatibility with its type system.
- **No Runtime Reflection**: We strictly avoid PHP's native reflection (`new ReflectionClass`). All information is extracted via static analysis (AOT).
- **Intermediate Representation (IR)**: We convert PHPStan types into a strictly typed Schema Object Model (DTOs) before generating JSON. This decouples the analysis logic from the output format.

## 2. Architectural Pipeline

We adopt a **3-Stage Compiler Pipeline** enhanced with a **Schema Registry** to handle recursion and references.

```mermaid
flowchart LR
    subgraph Phase1 [Phase 1: Analysis]
        A[PHPStan Engine] -->|Node| B(PropertyCollector)
    end
    
    subgraph Phase2 [Phase 2: Normalization]
        B -->|PHPStan Type| C{TypeMapper Dispatcher}
        C <-->|Query/Register| R[(SchemaRegistry)]
        C -->|Chain| D[DateMapper]
        C -->|Chain| E[RefMapper]
        C -->|Chain| F[PrimitiveMapper]
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
- **Key Decision**: Determines `required` vs `optional` status based on default values.

### 2.2. Phase 2: Normalization (Mapper)
- **Pattern**: **Chain of Responsibility**
- **Component**: `TypeMapper` (Dispatcher)
- **Responsibility**: Converts `PHPStan\Type` into **Schema DTOs**.
- **Workflow**:
    1.  **Special Types Check**: First, check for special types like `DateTime` (mapped to `string(date-time)`).
    2.  **Object/Class Check**: If it's a user-defined class, delegate to `SchemaRegistry` (returns `$ref`).
    3.  **Primitive Check**: Map `int`, `string`, `bool`, etc.
    4.  **Fallback**: Map unknown types to Empty Schema (`{}`) (Any type) with a warning.

### 2.3. Phase 3: Generation (Schema DTOs)
- **Component**: `src/Schema/*`
- **Responsibility**: Represents the JSON Schema structure in a strictly typed PHP Object Model.

### 2.4. Schema Registry & References
We employ a **Schema Registry** to manage type definitions, reusability, and recursion.

- **Component**: `SchemaRegistry`
- **Strategy**: **"Ref by Default"**
    - Every PHP Class (DTO) is treated as a reusable Schema Component.
    - Output Format: **Self-contained File** (Default).
        - Each generated JSON file contains the Root schema and a `definitions` section containing all referenced schemas (`GroupDto`, `EnumDto`, etc.).

## 3. Design Strategies

### 3.1. Required vs Nullable
We strictly separate "Required" (Key existence) and "Nullable" (Value type). 

| PHP Definition | Schema Meaning | Responsibility | Generated Schema |
| :--- | :--- | :--- | :--- |
| `public int $a;` | **Required**, Not Null | Collector: Required | `{"type": "integer"}`, `required: ["a"]` |
| `public ?int $a;` | **Required**, Nullable | Collector: Required | `{"type": ["integer", "null"]}`, `required: ["a"]` |
| `public int $a = 1;` | **Optional**, Not Null | Collector: Optional | `{"type": "integer"}` |

### 3.2. Naming Strategy
#### Property Names
- **MVP**: Use PHP property names as-is (e.g., `userProfile` -> `userProfile`).

#### Component Names (Schema Registry)
To ensure compatibility with OpenAPI component names (`^[a-zA-Z0-9\.\-_]+$`), we sanitize PHP Class names.
- **Strategy**: **FQCN Sanitization**
- **Rule**: Replace backslashes `\` with dots `.` (or hyphens `-`).
- **Example**: `App\DTO\User` -> `App.DTO.User`

### 3.3. Generics Strategy
OpenAPI 3.1 does not support Generics natively.
- **Strategy**: **Inline Expansion**
- **Rule**: Generic objects (e.g., `ApiResponse<User>`) are **NOT** registered as reusable components. They are expanded inline as `type: object` with their specific properties resolved in place.
- **Reasoning**: `ApiResponse<User>` and `ApiResponse<Post>` are distinct schemas and cannot share a single `$ref`.

### 3.4. Verification Strategy
- **Tool**: `ajv-cli` (Draft 2020-12)
- **Pipeline**: CI (GitHub Actions)
- **Process**: All generated JSON files in `tests/Fixtures/Expected` are validated against the JSON Schema Meta-Schema to ensure compliance.

## 4. Directory Structure

```text
src/
├── Collector/       # Phase 1: Extracts info from PHPStan
├── Mapper/          # Phase 2: Converts Types to Schema DTOs (Chain of Responsibility)
├── Schema/          # Phase 3: Schema Object Model (The IR)
├── Registry/        # Schema Management & Reference Resolution
└── Rule/            # Entrypoint: PHPStan Rule to trigger the pipeline
```