# Roadmap: PHPStan-First JSON Schema Generator

## Phase 1: Foundation (Completed) ✅
- [x] Project Skeleton (Git, License).
- [x] GitHub Governance (Templates, Security).

## Phase 2: Development Environment (Completed) ✅
- [x] PHP 8.5 Environment.
- [x] Docker & Buildx Bake Integration.
- [x] CI/CD Pipeline (GitHub Actions).
- [x] Strict Static Analysis (PHPStan Level Max).

## Phase 3: MVP - Primitive Types (In Progress) 🚧
- [ ] `string`, `int`, `bool`, `float` type mapping.
- [ ] Core logic using `BetterReflection`.
- *Note: Implementation is pending in PR #3.*

## Phase 4: Integer Constraints
- [ ] Support `int<min, max>` via `phpstan/phpdoc-parser`.
- [ ] Map to `minimum` and `maximum` in JSON Schema.

## Phase 5: String Constraints
- [ ] Support `non-empty-string` -> `minLength: 1`.
- [ ] Support string formats (e.g. email, uuid) via Attributes.

## Phase 6: Enums
- [ ] Support PHP 8.1 Backed Enums.
- [ ] Map to `enum: ["val1", "val2"]`.

## Phase 7: Nested Objects
- [ ] Recursive parsing of referenced classes.
- [ ] `$defs` generation and `$ref` linking.

## Phase 8: Attributes Customization
- [ ] Implement `#[JsonSchema]` attribute.
- [ ] Allow overriding `title`, `description`, `example`.

## Phase 9: Advanced Types (Future)
- [ ] Array Shapes (`array{foo: string}`).
- [ ] Generics (`Response<User>`).
- [ ] Nullable handling improvements.