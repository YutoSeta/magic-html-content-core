<?php

namespace MagicHtml\Content\Tests;

use MagicHtml\Content\SchemaDefinition;
use MagicHtml\Content\SchemaValidator;
use PHPUnit\Framework\TestCase;

final class SchemaValidatorTest extends TestCase
{
    public function test_it_validates_required_and_types(): void
    {
        $schema = new SchemaDefinition('invoice-template', '請求書', [
            'title' => ['type' => 'string', 'required' => true],
            'amount' => ['type' => 'number', 'required' => true],
        ]);
        $this->assertSame([], (new SchemaValidator)->validate($schema, ['title' => '請求書', 'amount' => 1000]));
        $this->assertArrayHasKey('value.amount', (new SchemaValidator)->validate($schema, ['title' => '請求書', 'amount' => '1000']));
    }
}
