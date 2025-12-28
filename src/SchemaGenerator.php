<?php

declare(strict_types=1);

namespace PhpJsonSchemaGenerator;

use PHPStan\PhpDocParser\Ast\Type\IntegerRangeTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionNamedType;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Roave\BetterReflection\Reflection\ReflectionType;
use Roave\BetterReflection\Reflector\DefaultReflector;
use Roave\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\ComposerSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\PhpInternalSourceLocator;

final class SchemaGenerator
{
    private PhpDocParser $phpDocParser;
    private Lexer $lexer;

    public function __construct()
    {
        $constExprParser = new ConstExprParser();
        $typeParser = new TypeParser($constExprParser);
        $this->phpDocParser = new PhpDocParser($typeParser, $constExprParser);
        $this->lexer = new Lexer();
    }

    /**
     * @return array<string, mixed>
     */
    public function generate(string $className): array
    {
        $astLocator = (new BetterReflection())->astLocator();
        $sourceStubber = (new BetterReflection())->sourceStubber();

        /** @var \Composer\Autoload\ClassLoader $loader */
        $loader = require __DIR__ . '/../vendor/autoload.php';

        $sourceLocator = new AggregateSourceLocator([
            new ComposerSourceLocator($loader, $astLocator),
            new PhpInternalSourceLocator($astLocator, $sourceStubber),
        ]);

        $reflector = new DefaultReflector($sourceLocator);
        $reflectionClass = $reflector->reflectClass($className);

        $properties = [];
        $required = [];

        foreach ($reflectionClass->getProperties() as $property) {
            $propertyName = $property->getName();
            $propertyType = $property->getType();

            $schema = $this->mapPropertyToSchema($property);
            $properties[$propertyName] = $schema;

            if ($propertyType !== null && !$propertyType->allowsNull()) {
                $required[] = $propertyName;
            }
        }

        return [
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
            'additionalProperties' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPropertyToSchema(ReflectionProperty $property): array
    {
        $type = $property->getType();
        $schema = $this->mapPhpTypeToJsonType($type);

        // Simple E2E implementation for int<min, max>
        $docComment = $property->getDocComment();
        if ($docComment !== '') {
            $tokens = new TokenIterator($this->lexer->tokenize($docComment));
            try {
                $phpDocNode = $this->phpDocParser->parse($tokens);
                foreach ($phpDocNode->getVarTagValues() as $varTag) {
                    if ($varTag->type instanceof IntegerRangeTypeNode) {
                        $min = $varTag->type->min;
                        $max = $varTag->type->max;
                        // Handle simple integer values
                        if (property_exists($min, 'value')) {
                            $schema['minimum'] = (int) $min->value;
                        }
                        if (property_exists($max, 'value')) {
                            $schema['maximum'] = (int) $max->value;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Ignore parsing errors for MVP
            }
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPhpTypeToJsonType(?ReflectionType $type): array
    {
        if (!$type instanceof ReflectionNamedType) {
            return ['type' => 'string'];
        }

        return match ($type->getName()) {
            'int' => ['type' => 'integer'],
            'float' => ['type' => 'number'],
            'bool' => ['type' => 'boolean'],
            'string' => ['type' => 'string'],
            default => ['type' => 'string'],
        };
    }
}