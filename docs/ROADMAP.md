# Project Roadmap

## Milestone 0.1.0: MVP (Minimum Viable Product)
Focus: Support basic scalar types, integer ranges, and simple DTOs using the new Domain-Driven Architecture.

### Phase 1: Domain Layer Foundation
- [ ] **Schema Interface & Metadata**
    - Define `Schema` interface (`JsonSerializable`).
    - Create `SchemaMetadata` VO for common fields (`description`, `deprecated`, etc.).
- [ ] **Concrete Schema Implementation**
    - `IntegerSchema` (supports min/max, default, enum).
    - `StringSchema` (supports pattern, format).
    - `ObjectSchema` (properties map).
    - `RefSchema` ($ref pointer).

### Phase 2: Service Layer & Logic
- [ ] **SchemaRegistry**
    - Implement caching mechanism.
    - Implement circular reference detection.
- [ ] **TypeMapper (Factory)**
    - Implement logic to convert PHPStan `Type` -> `Schema`.
    - Handle `IntegerRangeType` mapping.

### Phase 3: PHPStan Integration Refactoring
- [ ] **Refactor PropertyCollector**
    - Integrate `TypeMapper` to produce `PropertyDTO`.
- [ ] **Refactor SchemaAggregatorRule**
    - Remove ad-hoc JSON dumping.
    - Implement proper aggregation and file writing logic.

### Phase 4: Verification
- [ ] Update E2E tests (`SchemaExportTest`) to verify generated JSON structure.
