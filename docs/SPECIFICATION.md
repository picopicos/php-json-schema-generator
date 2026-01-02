# Specification & Roadmap

This document serves as the **Single Source of Truth** for the functional requirements and engineering roadmap of the PHPStan JSON Schema Generator.

---

## 1. Functional Specifications (User Features)

### 1.1 Primitive & Scalar Types
- [x] **Integer**: `int` -> `{"type": "integer"}
- [ ] **Float**: `float` -> `{"type": "number"}
- [ ] **String**: `string` -> `{"type": "string"}
- [ ] **Boolean**: `bool`, `true`, `false` -> `{"type": "boolean"}`
    - `true`, `false` literal types should map to const or enum if specific.
- [ ] **Null**: `null` -> `{"type": "null"}
- [ ] **Mixed**: `mixed` -> `{}` (Empty schema allowing anything)
- [ ] **Void/Never**: `void`, `never` -> Should probably be ignored or result in empty schema depending on context.
- [ ] **Scalar**: `scalar` -> `{"type": ["string", "number", "boolean"]}
- [ ] **Number**: `number` -> `{"type": "number"}` (int | float)

### 1.2 Integer Constraints & Ranges
Detailed mapping for PHPStan's integer range types.
- [x] **Range**: `int<min, max>` -> `minimum`, `maximum`
- [ ] **Min**: `int<min, max>` (one side) -> `minimum` or `maximum`
- [ ] **Positive**: `positive-int` (> 0) -> `type: integer`, `minimum: 1`
- [ ] **Negative**: `negative-int` (< 0) -> `type: integer`, `maximum: -1`
- [ ] **Non-Positive**: `non-positive-int` (<= 0) -> `type: integer`, `maximum: 0`
- [ ] **Non-Negative**: `non-negative-int` (>= 0) -> `type: integer`, `minimum: 0`
- [ ] **Int Mask**: `int-mask<...>` -> `enum` (List of possible calculated integer values) - *Low Priority*

### 1.3 String Constraints
- [ ] **Non-Empty**: `non-empty-string` -> `minLength: 1`
- [ ] **Numeric String**: `numeric-string` -> `type: string`, `pattern: "^[+-]?\\d+(\\.\\d+)?$"` (or similar regex)
- [ ] **Class String**: `class-string<T>` -> `type: string` (Consider adding custom format like `class-reference`)
- [ ] **Literal**: `string` literal (e.g. `'active'`) -> `const: 'active'` or `enum: ['active']`
- [ ] **Non-Falsy**: `non-falsy-string` -> `minLength: 1` (Excludes "0" and "")

### 1.4 Arrays & Lists
Distinguishing between JSON Arrays (List) and Objects (Map) is crucial.
- [ ] **List**: `list<T>` -> `{"type": "array", "items": {...}}` (Sequential, 0-indexed)
- [ ] **General Array**: `array<T>` -> `{"type": "array", "items": {...}}`
    - *Note*: PHPStan treats `array<T>` as map-capable, but JSON Schema usually maps explicit keys to objects. If strictly a map, see below.
- [ ] **Map (Associative)**: `array<string, T>` -> `{"type": "object", "additionalProperties": {...}}`
- [ ] **Array Shape (Sealed)**: `array{foo: int}` -> `{"type": "object", "properties": {"foo": ...}, "additionalProperties": false}`
- [ ] **Array Shape (Unsealed)**: `array{foo: int, ...}` -> `{"type": "object", "properties": {"foo": ...}, "additionalProperties": true}` (or schema for rest type)
- [ ] **Optional Keys**: `array{foo?: int}` -> Property "foo" excluded from `required`.
- [ ] **Non-Empty**: `non-empty-array`, `non-empty-list` -> `minItems: 1`

### 1.5 Objects & Classes
- [x] **DTO Class**: PHP Class -> `{"type": "object", "properties": ...}`
- [x] **Visibility**: Only `public` properties are exported (Private/Protected are ignored).
- [ ] **Nested Objects**: Property type is another Class -> Nested schema structure.
- [ ] **Recursive Reference**: Class A -> Class B -> Class A. Must use `{"$ref": "..."}` to prevent infinite recursion.
- [ ] **Generics**: `Response<User>` -> Resolved to concrete schema `Response` embedding `User`.

### 1.6 Logic & Advanced Types
- [ ] **Union**: `A|B` -> `anyOf: [SchemaA, SchemaB]`
- [ ] **Nullable**: `?T` (T|null) -> `type: ["...", "null"]` (Key remains in `required`)
- [ ] **Intersection**: `A&B` -> `allOf: [SchemaA, SchemaB]`
- [ ] **Value-of**: `value-of<CONST_ARRAY>` -> `enum` extraction from constant values.
- [ ] **Key-of**: `key-of<CONST_ARRAY>` -> `enum` extraction from constant keys.
- [ ] **Backed Enum**: `enum Status: string` -> `type: string`, `enum: [...]`
- [ ] **Unit Enum**: `enum Color` -> `type: string`, `enum: ['Red', 'Blue']` (Case names)

### 1.7 DateTime Support
- [ ] **DateTimeInterface**: `DateTime`, `DateTimeImmutable`, `Carbon` -> `{"type": "string", "format": "date-time"}`

### 1.8 Metadata & Attributes
- [ ] **Description (PHPDoc)**: `/** @description ... */` -> `description`
- [ ] **Description (Attribute)**: `#[Description('...')]` -> `description`
- [ ] **Deprecated**: `@deprecated` or `#[Deprecated]` -> `deprecated: true`
- [ ] **Example**: `#[Example(...)]` -> `examples`
- [ ] **Title**: Class name or `#[Title]` -> `title`

---

## 2. Implementation & Quality Design (Engineering Roadmap)

### 2.1 Architecture & Scalability
- [x] **Phase Separation**:
    - **Collector**: Analyzes code and serializes `Schema` objects (using `SchemaDTO`).
    - **Rule**: Aggregates data and triggers `SchemaWriter`.
- [x] **DTO Encapsulation**: `SchemaDTO` handles serialization logic (`base64` + `unserialize` with `allowed_classes`) to prevent object injection risks.
- [ ] **Reference Strategy ($ref)**:
    - **Issue**: Current implementation implies inline nesting. Large graphs will balloon file size, and recursion will crash.
    - **Solution**: Implement a `SchemaRegistry` or `RefStrategy` to determine if a schema should be inlined or referenced via `{"$ref": "#/definitions/..."}` or `{"$ref": "./OtherFile.json"}`.
- [ ] **Memory Management**:
    - **Issue**: Collecting all schemas in memory before writing might exceed RAM on massive projects.
    - **Solution**: Investigate incremental writing or streaming if `CollectedDataNode` becomes a bottleneck.

### 2.2 Configuration & DX (extension.neon)
The configuration is handled via PHPStan's parameter system. Users override specific keys in their `phpstan.neon`.

- [ ] **Configuration Schema**:
    - `outputDirectory`: (Existing) Path to write files.
    - `refStrategy`: `inline` | `file_system` | `definitions`
    - `format`: `json` | `yaml` (Future)
    - `prettyPrint`: `boolean` (Default: true)
    - `includePrivate`: `boolean` (Default: false) - Option to expose private props if explicitly requested.
- [ ] **Contextual Error Reporting**:
    - Use `RuleErrorBuilder` to point to specific lines (e.g., "Property $foo uses unsupported type 'resource'").

### 2.3 Security
- [x] **File Permissions**: Output directory creation restricted to `0755`.
- [x] **Path Traversal**: Filenames sanitized via `basename()` and regex validation.
- [x] **Object Injection**: `unserialize()` restricted to specific Schema classes in `SchemaDTO`.
- [ ] **Denial of Service**: Guard against extremely deep recursion depth during schema generation.

### 2.4 Testing Strategy
- [x] **Integration Testing**: End-to-end tests compiling PHP code and asserting JSON output.
- [ ] **Unit Testing**: 100% coverage for Mappers (e.g., `IntegerTypeMapper` handles all `int<...>` variants).
- [ ] **Snapshot Testing**: Use a snapshot tool (e.g., `spatie/phpunit-snapshot-assertions`) for complex JSON outputs to avoid brittle exact-match assertions in code.
- [ ] **Fixture Coverage**: Add fixtures for every checklist item in Section 1 (Generics, Recursive Classes, Complex Unions, Traits).

### 2.5 Documentation & Release Preparation
- [x] **Specification**: This document.
- [ ] **README**:
    - Installation (`composer require --dev`).
    - Configuration guide (`extension.neon`).
    - Supported Types Matrix.
- [ ] **CONTRIBUTING**: Guide for adding new `TypeMappers`.
- [ ] **Changelog**: Setup automated release notes (Release Drafter).