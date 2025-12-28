# Contributing

> [!WARNING]
> This project is in the early stages of development (**Pre-0.1.0**).
> While we appreciate interest, the architecture is still evolving.

## Requirements

- **Docker** (Recommended)
- OR: PHP 8.5+ and Composer

## Development

We strongly recommend using the provided Docker environment to ensure consistency.

### Using Docker (Recommended)

Wrapper scripts in `bin/` allow you to run commands inside the container easily.

```bash
# Install dependencies
./bin/composer install

# Run Tests
./bin/composer test

# Run Static Analysis (PHPStan)
./bin/composer lint

# Format Code (PHP-CS-Fixer)
./bin/composer format
```

### Using Local PHP

If you have PHP 8.5+ installed locally:

```bash
composer install
composer test
composer lint
composer format
```
