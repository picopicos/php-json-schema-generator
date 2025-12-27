<?php

declare(strict_types=1);

namespace Tests;

use PhpJsonSchemaGenerator\Placeholder;
use PHPUnit\Framework\TestCase;

/**
 * Placeholder test to pass PHPUnit execution during setup.
 */
final class PlaceholderTest extends TestCase
{
    public function testPlaceholderExists(): void
    {
        $placeholder = new Placeholder();
        $this->assertInstanceOf(Placeholder::class, $placeholder);
    }
}
