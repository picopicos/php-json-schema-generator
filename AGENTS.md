# Agent Instructions & Project Context

## Project Documentation
- **Architecture**: [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)
- **Roadmap**: [docs/ROADMAP.md](docs/ROADMAP.md)

## Project Mission
**"PHPStan-First JSON Schema Generator"**
Build a library that generates JSON Schema compatible with OpenAPI 3.1 directly from PHP classes (DTOs) using Static Analysis (AOT).
**Runtime reflection (`new ReflectionClass`) is strictly prohibited.**

## Tech Stack & Architecture
- **Language:** PHP 8.5+
- **Core Libraries:**
  - `roave/better-reflection`: For reading class structure without loading files.
  - `phpstan/phpdoc-parser`: For parsing complex PHPDoc types (e.g., `int<1, 10>`).
  - `nikic/php-parser`: Low-level AST handling.
- **Testing:** PHPUnit (TDD is mandatory).
- **Static Analysis:** PHPStan (Level Max).

## Coding Standards (Strict)
1.  **Strict Types:** All files must start with `declare(strict_types=1);`.
2.  **Immutability:** DTOs must be `readonly` classes. Properties should be `public readonly`.
3.  **No Runtime Reflection:** Always use `BetterReflection` or `phpdoc-parser`. Do not instantiate user classes during generation.
4.  **Final by Default:** Classes should be `final` unless inheritance is explicitly required.
5.  **Type Safety**: Avoid `mixed`. Always use specific types or narrow unions. For arrays, specify keys and value types clearly (e.g., `array<string, string|int>`).

## Development Workflow (TDD)
1.  **Create Fixture:**
    - `tests/Fixtures/Input/NameDto.php` (PHP Code)
    - `tests/Fixtures/Expected/name_dto.json` (Expected Output)
2.  **Write Test:** Create a test case in `tests/` asserting the conversion logic.
3.  **Implement:** Write code in `src/` to pass the test.
4.  **Refactor:** Ensure clean code and PHPStan compliance.

## Pull Request Workflow
1.  **Template**: Always use `.github/pull_request_template.md` when creating a PR.
2.  **Metadata**: Set **Assignee** (@me), **Labels** (e.g., `enhancement`, `documentation`), and **Milestone** (e.g., `0.1.0`) for every PR.
3.  **Synchronization**: Every time you push new changes to an existing PR, update the PR description using `gh pr edit` to reflect the latest state (Purpose, Details, Verification).
4.  **Context**: Ensure the PR body clearly explains the "Why" (Purpose) and "How" (Details).

## Documentation Maintenance
- **Keep Updated**: Whenever the architecture or roadmap changes, update `docs/ARCHITECTURE.md` and `docs/ROADMAP.md` within the same PR.
- **Accuracy**: These documents must reflect the *current* state of the codebase and project goals.

## Environment
- **Run Tests:** `./bin/composer test`
- **Run Lint:** `./bin/composer lint`
- **Run Format:** `./bin/composer format`
- **Docker:** `docker compose` is used for all commands via `bin/` wrappers.
