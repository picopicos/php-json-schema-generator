<?php

declare(strict_types=1);

namespace Tests\Constraints;

use InvalidArgumentException;
use JsonException;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\Constraint\Constraint;
use stdClass;
use Stringable;

class MatchesJsonSchema extends Constraint
{
    private object $schema;
    /** @var list<string> */
    private array $errors = [];

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string|object $schema)
    {
        // Decode if string, otherwise use object as is
        if (is_string($schema)) {
            try {
                $decoded = json_decode($schema, false, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new InvalidArgumentException('Schema string is not valid JSON: ' . $e->getMessage(), 0, $e);
            }
        } else {
            $decoded = $schema;
        }

        if (!$decoded instanceof stdClass) {
            throw new InvalidArgumentException('Schema must be a valid JSON object or string decoding to an object.');
        }

        $this->schema = $decoded;
    }

    /**
     * @param mixed $other The data to validate
     */
    protected function matches($other): bool
    {
        $validator = new Validator();

        // Validate $other (data) against $this->schema
        $result = $validator->validate($other, $this->schema);

        if (!$result->isValid()) {
            $error = $result->error();
            if ($error === null) {
                // Should not happen if isValid is false
                $this->errors = ['Unknown validation error'];
                return false;
            }

            $formatter = new ErrorFormatter();
            // Flatten errors for easier reporting
            $formattedErrors = $formatter->format($error);

            $this->errors = [];
            foreach ($formattedErrors as $formattedError) {
                // Ensure it's an array (Opis usually returns array of arrays, but safety first)
                if (!is_array($formattedError)) {
                    continue;
                }

                $msg = isset($formattedError['message']) && (is_scalar($formattedError['message']) || $formattedError['message'] instanceof Stringable)
                    ? (string) $formattedError['message']
                    : 'Unknown error';

                $ptr = 'root';
                if (isset($formattedError['dataPointer']) && is_array($formattedError['dataPointer'])) {
                    $ptrParts = [];
                    foreach ($formattedError['dataPointer'] as $part) {
                        $ptrParts[] = (is_scalar($part) || $part instanceof Stringable) ? (string) $part : json_encode($part);
                    }
                    $ptr = implode('/', $ptrParts);
                }

                $this->errors[] = sprintf("[%s] %s", $ptr, $msg);
            }

            return false;
        }

        return true;
    }

    public function toString(): string
    {
        return 'matches the provided JSON Schema';
    }

    /**
     * @param mixed $other
     */
    protected function additionalFailureDescription($other): string
    {
        if (empty($this->errors)) {
            return '';
        }

        $messages = ["Validation Errors:"];
        foreach ($this->errors as $msg) {
            $messages[] = "  " . $msg;
        }

        // Add a snippet of the data for context (pretty printed)
        $messages[] = "\nData provided:";
        $messages[] = json_encode($other, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return implode("\n", $messages);
    }
}
