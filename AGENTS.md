# Agent Instructions & Project Context

## Project Documentation
- **Architecture**: [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)
- **Roadmap**: [docs/ROADMAP.md](docs/ROADMAP.md)

## Project Mission
**"PHPStan-First JSON Schema Generator"**
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
5.  **Type Safety:** Avoid `mixed`. Always use specific types or narrow unions.

## Development Workflow (TDD)
1.  **Create Fixture:**
    - `tests/Fixtures/Input/NameDto.php` (PHP Code)
    - `tests/Fixtures/Expected/name_dto.json` (Expected Output)
2.  **Write Test:** Create a test case asserting the extraction logic.
3.  **Implement:** Write the PHPStan Rule to export the correct type data.
4.  **Refactor:** Ensure clean code.

## Pull Request Workflow
1.  **Template**: Always use `.github/pull_request_template.md`.
2.  **Metadata**: Set **Assignee**, **Labels**, and **Milestone**.
3.  **Synchronization**: Keep PR description updated.
4.  **Context**: Explain "Why" and "How".

## Documentation Maintenance
- **Keep Updated**: Sync `docs/ARCHITECTURE.md` with code changes.

## Environment
- **Run Tests:** `./bin/composer test`
- **Run Lint:** `./bin/composer lint`
- **Run Format:** `./bin/composer format`
- **Docker:** `docker compose` is used via `bin/` wrappers.