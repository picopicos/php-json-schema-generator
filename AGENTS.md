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
