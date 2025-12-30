# Copilot Instructions for phpstan-json-schema

## Project Context
A PHP 8.5+ library for generating JSON Schema (Draft 2020-12) from PHP classes using static analysis (roave/better-reflection, phpstan/phpdoc-parser).

## Core Principles
1.  **Strict Typing**: Always use `declare(strict_types=1);`. Avoid `mixed` unless absolutely necessary.
2.  **Immutability**: Prefer `readonly` classes and properties.
3.  **No Runtime Reflection**: Never use `ReflectionClass` from PHP's core. Always use `Roave\BetterReflection`.
4.  **Static Analysis First**: Follow PHPStan (Level Max) and Bleeding Edge rules.
5.  **TDD**: Write unit tests with fixtures (Input PHP / Expected JSON).

## Coding Standards
- Follow `@PER-CS2.0` coding standard.
- Use PSR-4 for autoloading.
- Classes should be `final` by default.
- Use PHP 8.3/8.4/8.5 features (e.g., typed constants, property hooks if applicable).
