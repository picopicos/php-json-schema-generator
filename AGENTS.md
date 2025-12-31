# Agent Instructions & Project Context

## Project Documentation
- **Architecture**: [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)
- **Roadmap**: [GitHub Milestone 0.1.0](https://github.com/picopicos/phpstan-json-schema/milestone/1)

## Project Mission
**"PHPStan JSON Schema Generator"**
Build a library that generates JSON Schema compatible with OpenAPI 3.1 directly from PHP classes (DTOs) using **PHPStan's native analysis engine**.

## Tech Stack & Architecture
- **Language:** PHP 8.5+
- **Core Engine:** `phpstan/phpstan` (Custom Rule / Extension).
- **Testing:** PHPUnit (TDD is mandatory).

## Coding Standards (Strict)
1.  **Strict Types:** All files must start with `declare(strict_types=1);`.
2.  **Immutability:** DTOs must be `readonly` classes. Properties should be `public readonly`.
3.  **No Runtime Reflection:** Always use PHPStan's `Type` objects.
4.  **Final by Default:** Classes should be `final`.
5.  **Type Safety:** Avoid `mixed`. Always use specific types or narrow unions. For arrays, specify keys and value types clearly (e.g., `array<string, string|int>`).
6.  **Array Shapes:** When returning arrays with known keys (especially in `jsonSerialize`), always use PHPStan Array Shapes (e.g., `array{type: string, min?: int}`) instead of generic `array<string, mixed>`.
7.  **Type Aliases:** Use `@phpstan-type` to define reusable array shapes. Name these types using **snake_case** (e.g., `schema_metadata_json`) to clearly distinguish them from class names.
8.  **Data Providers:** Use `yield` (Generators) for PHPUnit Data Providers instead of returning arrays. Use `iterable` return types.
    - **Usage**: For input/output validation or transformation logic, always prefer Data Providers over repetitive test methods.
    - **Coverage**: Ensure comprehensive coverage, including happy paths, edge cases, and strictly defined **boundary values** (min/max, off-by-one errors).
    - **Named Arguments**: Use **string keys** in the yielded array that match the test method's parameter names. This enables PHPUnit Named Arguments, making tests independent of parameter order and improving clarity.
    - **Type Safety**: Use `@phpstan-param` and `@phpstan-return` with Data Providers to ensure type safety between the provider and the test method.
9.  **Test Implementation:**
    - **Structure Comparison**: Use `assertSame` with full array structures to verify JSON serialization results in a single, clear assertion.
    - **Narrowing Types**: Use runtime `assert()` (e.g., `assert(is_array($data))`) instead of `/** @var */` to inform PHPStan about types in test methods.
    - **Custom Assertions**: Reuse common logic (like JSON Schema validation) via Traits and Custom PHPUnit Constraints.
10. **No `@var` Casting:** Do not use `/** @var Type $var */` to force type overrides, as it suppresses static analysis errors (similar to `as Type` in TS). Instead, use runtime assertions (`assert($var instanceof Type)`) or proper type checks (`if (!is_array($var)) ...`) to narrow types safely.
11. **Exception Documentation:** Always document exceptions using `@throws` annotations in the PHPDoc block for any method that explicitly throws an exception or propagates a specific one.

## Development Workflow (TDD)
1.  **Create Fixture:**
    - `tests/Fixtures/Input/NameDto.php` (PHP Code)
    - `tests/Fixtures/Expected/name_dto.json` (Expected Output)
2.  **Write Test:** Create a test case asserting the extraction logic.
3.  **Implement:** Write the PHPStan Rule to export the correct type data.
4.  **Refactor:** Ensure clean code.

## Pull Request Workflow
1.  **Template**: Always use `.github/pull_request_template.md` when creating a PR.
2.  **Metadata**: Set **Assignee** (@me), **Labels** (e.g., `enhancement`, `documentation`), and **Milestone** (e.g., `0.1.0`) for every PR.
3.  **Synchronization**: Every time you push new changes to an existing PR, update the PR description using `gh pr edit` to reflect the latest state (Purpose, Details, Verification).
4.  **Context**: Ensure the PR body clearly explains the "Why" (Purpose) and "How" (Details).

## Documentation Maintenance
- **Keep Updated**: Sync `docs/ARCHITECTURE.md` with code changes.
- **Milestone Updates**: Update the GitHub Milestone description to reflect progress, new tasks, and technical considerations.
- **Source of Truth**: The GitHub Milestone is the single source of truth for the project roadmap.

## Environment
- **Run Tests:** `./bin/composer test`
- **Run Lint:** `./bin/composer lint`
- **Run Format:** `./bin/composer format`
- **Docker:** `docker compose` is used via `bin/` wrappers.
