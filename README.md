# PHPStan JSON Schema Generator

**Generate OpenAPI-compatible JSON Schemas from PHP classes using PHPStan's native type inference.**

<p align="center">
  <a href="https://github.com/picopicos/phpstan-json-schema/actions/workflows/ci.yml">
    <img src="https://github.com/picopicos/phpstan-json-schema/actions/workflows/ci.yml/badge.svg" alt="CI">
  </a>
  <a href="https://codecov.io/gh/picopicos/phpstan-json-schema">
    <img src="https://codecov.io/gh/picopicos/phpstan-json-schema/graph/badge.svg?token=YVHC1U9JXC" alt="Codecov"/>
  </a>
  <img src="https://img.shields.io/badge/php-%5E8.5-777bb4.svg" alt="PHP Version">
  <a href="https://github.com/picopicos/phpstan-json-schema/blob/main/LICENSE">
    <img src="https://img.shields.io/github/license/picopicos/phpstan-json-schema" alt="License">
  </a>
  <img src="https://img.shields.io/badge/PHPStan-level%20max-brightgreen.svg" alt="PHPStan Level">
</p>

## Features

- ✅ **Static Analysis Native**: Extracts types directly from PHPStan's AST, requiring no runtime class loading.
- ✅ **Strictly Typed**: Uses PHPStan's `Type` system instead of regex parsing.
- ✅ **Ref by Default**: Automatically generates reusable component schemas (`$ref`) to handle recursion.
- ✅ **Generics Support**: Correctly handles `list<User>`, `array<string, int>`, and `Collection<T>`.
- ✅ **Shape Support**: Supports `array{id: int, name: string}` without creating DTO classes.
- ✅ **OpenAPI 3.1**: Generates modern JSON Schema compatible with OpenAPI 3.1.

## Installation

```bash
composer require --dev picopicos/phpstan-json-schema
```

## Usage

(Coming Soon)

## Documentation

- [Architecture](docs/ARCHITECTURE.md)
- [Roadmap](docs/ROADMAP.md)

## License

MIT