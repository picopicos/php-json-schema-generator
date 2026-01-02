# Specification & Roadmap

This document serves as the **Single Source of Truth** for the functional requirements and engineering roadmap of the PHPStan JSON Schema Generator.

---

## 0. Target Dialect & Compatibility Strategy

Since JSON Schema and OpenAPI have different capabilities, the generator must support configurable dialects.

### 0.1 Dialects
- **JSON Schema 2020-12** (Default): Full feature set (`prefixItems`, `$defs`, `unevaluatedProperties`).
- **OpenAPI 3.1**: Mostly compatible with 2020-12.
- **OpenAPI 3.0**: Restricted feature set (`nullable: true`, no `prefixItems`, `items` must be object).

### 0.2 Feature Capability Matrix
| Feature | JSON Schema 2020-12 | OpenAPI 3.1 | OpenAPI 3.0 |
| :--- | :--- | :--- | :--- |
| **Nullable** | `type: ["string", "null"]` | `type: ["string", "null"]` | `type: "string", nullable: true` |
| **Tuple** | `prefixItems: [...]` | `prefixItems: [...]` | **Not Supported** (Fallback to `oneOf` or loose `items`) |
| **Const** | `const: "value"` | `const: "value"` | `enum: ["value"]` |
| **Intersection** | `unevaluatedProperties: false` | `unevaluatedProperties: false` | **Broken** with `additionalProperties: false` |

---

## 1. Type Mapping Specification

### 1.1 Primitive & Scalar Types
- [x] **Integer**: `int` -> `{"type": "integer"}"
- [ ] **Float**: `float` -> `{"type": "number"}"
- [ ] **String**: `string` -> `{"type": "string"}"
- [ ] **Boolean**: `bool` -> `{"type": "boolean"}"
    - Literal `true`/`false` -> `const: true` / `const: false` (or `enum` for OpenAPI 3.0).
- [ ] **Null**: `null` -> `{"type": "null"}"
- [ ] **Mixed**: `mixed` -> `{}` (Empty schema allowing anything).
- [ ] **Void/Never**: `void`, `never` -> Treated as non-existent property (ignored).
- [ ] **Scalar**: `scalar` -> `{"type": ["string", "number", "boolean"]}"
- [ ] **Number**: `number` -> `{"type": "number"}` (int | float)

### 1.2 Integer & Number Constraints
- [x] **Range**: `int<min, max>` -> `minimum`, `maximum`
- [ ] **Positive**: `positive-int` -> `minimum: 1`
- [ ] **Negative**: `negative-int` -> `maximum: -1`
- [ ] **Non-Positive**: `non-positive-int` -> `maximum: 0`
- [ ] **Non-Negative**: `non-negative-int` -> `minimum: 0`
- [ ] **Literal**: `1|2|3` -> `enum: [1, 2, 3]`
- [ ] **Non-Zero**: `non-zero-int` -> `not: {const: 0}` (or ignored in loose mode).

### 1.3 String Constraints
- [ ] **Non-Empty**: `non-empty-string` -> `minLength: 1`
- [ ] **Non-Falsy**: `non-falsy-string` -> `allOf: [{minLength: 1}, {not: {const: "0"}}]` (Strict mode only).
- [ ] **Numeric String**: `numeric-string` -> `type: string`, `pattern: "^[+-]?\\d+(\\.\\d+)?$"` (Approximation).
    - *Note*: This pattern is a lossy approximation of PHP's `is_numeric()`. Validation parity with PHP is **NOT** guaranteed (e.g., `1e10`, `0xFF` might not match).
- [ ] **Class String**: `class-string<T>` -> `type: "string"` (Optionally format: `class-reference`).
- [ ] **Literal**: `'active'|'inactive'` -> `enum: ['active', 'inactive']`.
- [ ] **Pattern**: *Future Scope* (Regex extraction).
- [ ] **Special Types**: `callable-string`, `lowercase-string`, `literal-string` -> `type: "string"` (Metadata/Comment only).

### 1.4 Arrays, Lists & Iterables
**Strict Policy**: PHP Arrays are ambiguous. We apply the following decision tree:

1. **List (`list<T>`)**: -> `{"type": "array", "items": T}` (Sequential, 0-indexed).
2. **Map (`array<string, T>`)**: -> `{"type": "object", "additionalProperties": T}`.
3. **Tuple (`array{T1, T2}`)**: -> `{"type": "array", "prefixItems": [T1, T2], "items": false}` (Dialect dependent).
4. **General Array (`array<T>`)**:
    - Default: Treat as `List`.
    - Configurable: `anyOf: [List, Map]` (Safe but complex).
5. **Array Key (`array<array-key, T>`)**: -> `anyOf: [List, Map]`.
6. **Iterable (`iterable<K, V>`)**: -> Treated same as `array<K, V>`.

### 1.5 Objects, Shapes & Classes
- [x] **DTO Class**: PHP Class -> `{"type": "object", "properties": ...}`
- [x] **Visibility**: Only `public` properties are exported.
- [ ] **Object Shape**: `object{foo: int, bar?: string}` -> Treated same as DTO Class (Inline object).
    - *Required Rule*: Follows array shape semantics. `foo` is required, `bar` is optional.
- [ ] **Magic Properties**: `@property` tags in PHPDoc -> Included in `properties` (Requires ClassReflection analysis).
- [ ] **Readonly**: `readonly` properties -> `readOnly: true`.
- [ ] **Generics**: `Response<User>` -> Schema name mangled to `Response_User` or `ResponseOfUser`.

### 1.6 Logic & Advanced Types
- [ ] **Union (`A|B`)**:
    - **Nullable**: `T|null` -> `type: ["T", "null"]` (or `nullable: true`).
    - **General**: `anyOf: [A, B]`. (`oneOf` is strictly better but computationally expensive to prove disjointness).
- [ ] **Intersection (`A&B`)**:
    - **Object Merging**: If A and B are both objects, **MERGE** properties into a single schema instead of `allOf`.
    - *Reason*: `allOf` with `additionalProperties: false` causes validation failure (A rejects B's props).
- [ ] **Type Alias**: `@phpstan-type` -> Resolves to the underlying type (Inline). *Future: Export as definition*.
- [ ] **Key-of / Value-of**: `key-of<T>` / `value-of<T>` -> Resolved to `enum`.

---

## 2. Architecture & Engineering Roadmap

### 2.1 Entry Points & Output Strategy
- [x] **Primary Entry Point**: `phpstan analyse` command runs the Collector/Rule.
    - *Future*: Standalone CLI or API invocation.
- [ ] **Target Selection**:
    - Currently: All classes in analysed paths.
    - Planned: Filter by Attribute `#[JsonSchema]` or namespace configuration.
- [x] **File Naming**: `App\Dto\User` -> `App.Dto.User.json`.

### 2.2 Reference Management ($ref)
- [ ] **Recursion Detection**:
    - Maintain a stack of types currently being resolved.
    - If recursion detected: Stop inline expansion, emit `{"ref": "..."}`.
- [ ] **Definition ID**:
    - Inline: `#/definitions/ClassName`
    - External: `ClassName.json`
    - Strategy: Configurable (`inline_defs` vs `file_system`).

### 2.3 Required vs Nullable Policy
JSON Schema distinguishes "missing key" from "null value".
- **Required**: Property must be present.
- **Nullable**: Property value can be null.

**Policy**:
1. **Required**: By default, all typed properties are `required` unless:
    - It is an optional array key (`key?: T`).
    - It has a default value (e.g., `public int $id = 0;`).
    - *Config option*: `required_policy: strict` (everything required) vs `loose`.
2. **Nullable**: `?T` does **NOT** remove from `required`. It maps to `type: ["...", "null"]`.

### 2.4 Error Handling
- [x] **Fail Loudly**: Unsupported types throw exceptions (catchable in tests).
- [ ] **Unsupported Policy**: Configurable (`error`, `warn`, `ignore`, `string_fallback`).
    - `callable` -> `ignore` (remove from properties).
    - `resource` -> `ignore`.

### 2.5 Security
- [x] **File Permissions**: Output directory `0755`.
- [x] **Path Traversal**: Filenames sanitized via `basename()`.
- [x] **Object Injection**: `unserialize()` allowed classes restricted.

### 2.6 Testing
- [x] **Integration Testing**: Verify JSON output against fixtures.
- [ ] **Snapshot Testing**: Use snapshots for complex schemas.
- [ ] **Dialect Testing**: Verify outputs against multiple dialects (2020-12 vs OpenAPI 3.0).

---

## 3. Non-Goals (Out of Scope)
- **Runtime JSON Shape**: We do not respect `JsonSerializable` or `__debugInfo`. This tool generates schemas based on **static PHP Types**, not runtime serialization behavior.
- **Serializer Metadata**: We do not parse `@Serializer\Groups` or `@JsonProperty` (JMS/Symfony). This tool generates schemas based on **PHP Types**, not serialization rules.
- **Runtime Validation**: This tool generates schemas; it does not validate data at runtime.