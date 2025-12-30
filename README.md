# PHPStan JSON Schema Generator

**Generate OpenAPI-compatible JSON Schemas from PHP classes using PHPStan's native type inference.**

[![CI](https://github.com/picopicos/phpstan-json-schema/actions/workflows/ci.yml/badge.svg)](https://github.com/picopicos/phpstan-json-schema/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/picopicos/phpstan-json-schema/graph/badge.svg?token=YOUR_TOKEN)](https://codecov.io/gh/picopicos/phpstan-json-schema)

## Why this library?

Unlike other libraries that rely on **Runtime Reflection**, this library uses **Static Analysis** (PHPStan) to extract types.

| Feature | Runtime Reflection Approach | **This Library (PHPStan Native)** |
| :--- | :--- | :--- |
| **Analysis** | Requires class loading (`require`) | **Zero-Runtime** (Source code only) |
| **Safety** | Side-effects possible during load | **Safe** (Code never executes) |
| **Type Support** | Limited (Generics lost at runtime) | **Full Support** (Generics, Shapes, Conditional Types) |
| **Environment** | Application Runtime | CI / CLI / Build Process |

## Features

- ✅ **Strictly Typed**: Uses PHPStan's `Type` system (no regex parsing of DocBlocks).
- ✅ **Ref by Default**: Generates reusable component schemas (`$ref`) automatically.
- ✅ **Generics Support**: Correctly handles `list<User>`, `array<string, int>`, `Collection<T>`.
- ✅ **Shape Support**: Supports `array{id: int, name: string}` without creating classes.
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