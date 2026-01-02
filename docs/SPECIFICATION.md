# Functional Specifications & Checklist

This document serves as the canonical feature checklist and roadmap for the PHPStan JSON Schema Generator.
It must be updated with every feature implementation. All checked items must have corresponding test coverage.

## 1. Primitive Types (基本型)
- [x] **Integer**: `int` -> `{"type": "integer"}`
- [ ] **String**: `string` -> `{"type": "string"}`
- [ ] **Boolean**: `bool` -> `{"type": "boolean"}`
- [ ] **Float**: `float` -> `{"type": "number"}`
- [ ] **Mixed**: `mixed` -> `{}` (Empty schema allowing anything)
- [ ] **Null**: `null` -> `{"type": "null"}` (Standalone use)

## 2. Integer Constraints (整数制約)
- [x] **Range**: `int<min, max>` -> `minimum`, `maximum`
- [ ] **Min**: `int<min, max>` (one side) -> `minimum` or `maximum`
- [ ] **Positive**: `positive-int` (> 0) -> `minimum: 1`
- [ ] **Negative**: `negative-int` (< 0) -> `maximum: -1`
- [ ] **Non-Positive**: `non-positive-int` (<= 0) -> `maximum: 0`
- [ ] **Non-Negative**: `non-negative-int` (>= 0) -> `minimum: 0`

## 3. String Constraints (文字列制約)
- [ ] **Non-Empty**: `non-empty-string` -> `minLength: 1`
- [ ] **Literal**: `string` literal (e.g. `'active'`) -> `const` or `enum`
- [ ] **Class String**: `class-string` -> `{"type": "string"}` (Ideally treated as generic string unless refined)
- [ ] **UUID**: (Future) Detect UUID patterns?

## 4. Collections & Arrays (コレクション)
- [ ] **List**: `list<T>` -> `{"type": "array", "items": {...}}` (Sequential array)
- [ ] **Array**: `array<T>` -> `{"type": "array", "items": {...}}` (Same as list in JSON Schema)
- [ ] **Map**: `array<string, T>` -> `{"type": "object", "additionalProperties": {...}}`
- [ ] **Shape**: `array{foo: int}` -> `{"type": "object", "properties": {"foo": ...}}`
- [ ] **Shape Optional**: `array{foo?: int}` -> Property "foo" is excluded from `required`.
- [ ] **Non-Empty**: `non-empty-list`, `non-empty-array` -> `minItems: 1`

## 5. Objects & Classes (オブジェクト)
- [x] **DTO Class**: PHP Class -> `{"type": "object", "properties": {...}, "additionalProperties": false}`
- [x] **Visibility**: Only `public` properties are included.
- [ ] **Nested DTO**: Property with Class type -> Nested object schema (inline or ref).
- [ ] **Recursive**: Class referring to itself -> Must use `$ref` to avoid infinite recursion.
- [ ] **Generics**: `Response<User>` -> Schema for `Response` with `User` schema embedded.

## 6. Advanced Types & Enums (高度な型)
- [ ] **Union**: `A|B` -> `anyOf` (or `type: [...]` for simple scalars).
- [ ] **Nullable**: `?T` (T|null) -> `type: ["...", "null"]`. Key remains in `required`.
- [ ] **Intersection**: `A&B` -> `allOf`.
- [ ] **Backed Enum**: `enum Status: string` -> `{"type": "string", "enum": ["active", "inactive"]}`.
- [ ] **Unit Enum**: `enum Color` -> `{"enum": ["Red", "Blue"]}` (Treat case names as strings).

## 7. DateTime (日付・時刻)
- [ ] **DateTimeInterface**: `DateTime`, `DateTimeImmutable` -> `{"type": "string", "format": "date-time"}`.

## 8. Metadata & Attributes (メタデータ)
- [ ] **Description (PHPDoc)**: `/** @description ... */` -> `description`.
- [ ] **Description (Attribute)**: `#[Description('...')]` -> `description`.
- [ ] **Deprecated**: `@deprecated` or `#[Deprecated]` -> `deprecated: true`.
- [ ] **Examples**: `#[Example(...)]` -> `examples`.
- [ ] **Title**: Class name or `#[Title]` -> `title`.

## 9. Configuration & Environment (設定・環境)

### System Requirements
- [x] **PHPStan Version**: Requires `phpstan/phpstan: ^2.0`.
- [x] **PHP Version**: Requires PHP 8.4+.

### Configuration (`extension.neon`)
The `extension.neon` file acts as the plugin entry point. Users can override specific settings in their `phpstan.neon` without redefining the entire configuration.

#### Available Parameters (`phpstanJsonSchema`)
- [x] **outputDirectory**:
    - **Type**: `string`
    - **Default**: `%currentWorkingDirectory%/output`
    - **Description**: The directory where JSON Schema files will be generated.
- [ ] **refStrategy** (Planned):
    - **Type**: `enum('inline', 'ref')`
    - **Default**: `inline`
    - **Description**: Determines whether nested objects are expanded inline or referenced via `$ref`.
- [ ] **prettyPrint** (Planned):
    - **Type**: `boolean`
    - **Default**: `true`
    - **Description**: Whether to format the JSON output with whitespace/indentation.

#### How Configuration Works
The extension defines a `parametersSchema` which validates user inputs. Users only need to specify parameters they wish to override.

**Example `phpstan.neon` usage:**
```neon
includes:
    - vendor/picopicos/phpstan-json-schema/extension.neon

parameters:
    phpstanJsonSchema:
        outputDirectory: 'schemas/generated'
        # Other parameters can be omitted to use defaults
```
