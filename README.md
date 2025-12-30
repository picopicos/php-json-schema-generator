# PHPStan JSON Schema Generator

**Generate OpenAPI-compatible JSON Schemas from PHP classes using PHPStan's native type inference.**

[![CI](https://github.com/picopicos/phpstan-json-schema/actions/workflows/ci.yml/badge.svg)](https://github.com/picopicos/phpstan-json-schema/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/picopicos/phpstan-json-schema/graph/badge.svg?token=YOUR_TOKEN)](https://codecov.io/gh/picopicos/phpstan-json-schema)

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
