# Architecture: PHPStan-First JSON Schema Generator

## 1. Core Concept

This library generates JSON Schema directly from PHP code by performing static analysis on type hints and DocBlocks. It is designed to bridge the gap between PHPStan's advanced type system and standard JSON Schema.

### Key Principles
- **Static Analysis (AOT)**: Uses static analysis to generate schemas. No code is executed during generation, ensuring safety and performance. Runtime reflection (`ReflectionClass`) is strictly prohibited.
- **PHPStan Compatibility**: Respects PHPStan's type system. Types like `int<1, 10>` or `non-empty-string` are converted to JSON Schema validation constraints (`minimum`, `minLength`).
- **Standard Compliance**: Output is compliant with standard JSON Schema and OpenAPI 3.1.

## 2. Component Architecture

The generation process follows a linear pipeline:

```mermaid
graph LR
    A[PHP Class] -->|BetterReflection| B(Class Structure)
    B -->|PHPDoc Parser| C(AST Type Nodes)
    C -->|Mapper| D(JSON Schema Array)
    D -->|Encoder| E[JSON File]
```

### 2.1. Reflector (`Roave\BetterReflection`)
The entry point. It reads PHP files and constructs an object model of classes, properties, and methods without loading them into PHP's memory.

### 2.2. Type Parser (`phpstan/phpdoc-parser`)
Extracts rich type information from DocBlocks.
- **Input**: `/** @var int<1, 10> $age */`
- **Output**: `IntegerRangeTypeNode(min: 1, max: 10)`

### 2.3. Schema Generator (Mapper)
The core logic layer. It traverses the class structure and maps PHP types to JSON Schema keywords.
- `int` -> `type: integer`
- `string` -> `type: string`
- `?string` -> `type: ["string", "null"]`
- `MyClass` -> `$ref: "#/$defs/MyClass"`

## 3. Development Workflow

We adopt a **Test-Driven Development (TDD)** approach using Fixtures.

1.  **Define Input**: Create a PHP class in `tests/Fixtures/Input/`.
2.  **Define Expected Output**: Create the corresponding JSON file in `tests/Fixtures/Expected/`.
3.  **Implement**: Write the parser logic to make the input match the output.

## 4. Why this architecture?

| Feature | Runtime Reflection Approach | Static Analysis (This Lib) |
| :--- | :--- | :--- |
| **Runtime Overhead** | **High** (Parses code on every request) | **Zero** (Uses pre-generated schema) |
| **Safety** | Execution side-effects possible | **Zero side-effects** (Code is never run) |
| **Type Detail** | Low (only native types) | **High** (Generics, Ranges, Shapes) |
| **Dependencies** | Requires autoloader | **Standalone** |

By choosing Static Analysis and AOT generation, we ensure:
- **Maximum Runtime Performance**: The application only validates against a static JSON file.
- **Advanced Type Support**: We can support complex types (Generics, Shapes) that the PHP runtime ignores.
