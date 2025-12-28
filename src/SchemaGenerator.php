<?php

declare(strict_types=1);

namespace PhpJsonSchemaGenerator;

use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\Type\ConstTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionNamedType;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Roave\BetterReflection\Reflection\ReflectionType;
use Roave\BetterReflection\Reflector\DefaultReflector;
use Roave\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\ComposerSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\PhpInternalSourceLocator;
use Throwable;

final class SchemaGenerator
{
    private PhpDocParser $phpDocParser;
    private Lexer $lexer;

    public function __construct()
    {
        $config = new ParserConfig([]);
        $constExprParser = new ConstExprParser($config);
        $typeParser = new TypeParser($config, $constExprParser);
        $this->phpDocParser = new PhpDocParser($config, $typeParser, $constExprParser);
        $this->lexer = new Lexer($config);
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

        $docComment = $property->getDocComment();
        if ($docComment !== null && $docComment !== '') {
            $tokens = new TokenIterator($this->lexer->tokenize($docComment));
            try {
                $phpDocNode = $this->phpDocParser->parse($tokens);
                foreach ($phpDocNode->children as $child) {
                    if ($child instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode) {
                        if ($child->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode) {
                            $this->applyTypeToSchema($child->value->type, $schema);
                        }
                    }
                }
            } catch (Throwable) {
                // Ignore
            }
        }

        return $schema;
    }

    private function applyTypeToSchema(\PHPStan\PhpDocParser\Ast\Type\TypeNode $typeNode, array &$schema): void
    {
        echo "Applying type: " . get_class($typeNode) . "\n";
        if ($typeNode instanceof GenericTypeNode) {
            $baseType = $typeNode->type;
            echo "  Base Type: " . $baseType->name . "\n";
            if ($baseType instanceof IdentifierTypeNode && ($baseType->name === 'int' || $baseType->name === 'integer')) {
                foreach ($typeNode->genericTypes as $idx => $gt) {
                    echo "    Arg $idx: " . get_class($gt) . "\n";
                    if ($gt instanceof ConstTypeNode) {
                        $constNode = $gt->const;
                        echo "      Const Node: " . get_class($constNode) . "\n";
                        if ($constNode instanceof ConstExprIntegerNode) {
                            $key = ($idx === 0) ? 'minimum' : 'maximum';
                            $schema[$key] = (int) $constNode->value;
                            echo "      -> $key: " . $constNode->value . "\n";
                        }
                    }
                }
            }
        }
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
