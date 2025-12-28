> [!WARNING]
> **Work In Progress**
> This project is currently under active development towards the **0.1.0** release.
> APIs and behaviors are subject to change without notice. Please wait for the initial release before using in production.

<p align="center">
  <h1 align="center">PHP JSON Schema Generator</h1>
  <p align="center">
    <strong>Generates JSON Schema directly from PHPStan type definitions using strict static analysis.</strong>
  </p>
  <p align="center">
    <a href="https://github.com/picopicos/php-json-schema-generator/actions/workflows/ci.yml">
      <img src="https://github.com/picopicos/php-json-schema-generator/actions/workflows/ci.yml/badge.svg" alt="CI">
    </a>
    <img src="https://img.shields.io/badge/php-%5E8.5-777bb4.svg" alt="PHP Version">
    <a href="https://github.com/picopicos/php-json-schema-generator/blob/main/LICENSE">
      <img src="https://img.shields.io/github/license/picopicos/php-json-schema-generator" alt="License">
    </a>
    <img src="https://img.shields.io/badge/PHPStan-level%20max-brightgreen.svg" alt="PHPStan Level">
    <img src="https://img.shields.io/badge/code%20style-php--cs--fixer-fab005.svg" alt="Code Style">
  </p>
</p>

## Key Features

- **🛡️ PHPStan Native**: Runs as a PHPStan Extension, guaranteeing 100% compatibility with your existing type definitions.
- **🔍 Advanced Type Support**: Automatically converts complex types like `int<min, max>` or `non-empty-string` into JSON Schema constraints using PHPStan's powerful inference engine.
- **🚀 Zero Runtime Overhead**: Designed to be used during build time or via CLI to dump schema files. No runtime reflection is used.

## Requirements

- **Environment (Build time)**: PHP 8.5+ is required to run the generator.
- **Target Code (Input)**: Supports PHP 8.0+ classes.

## License

MIT
