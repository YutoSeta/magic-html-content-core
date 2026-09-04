<?php

namespace MagicHtml\Content\Tests;

use MagicHtml\Content\SchemaDefinition;
use MagicHtml\Content\SchemaCompiler;
use PHPUnit\Framework\TestCase;

final class SchemaValidatorTest extends TestCase
{
    public function test_it_compiles_laravel_validation_rules(): void
    {
        $schema = new SchemaDefinition('invoice-template', '請求書', [
            'title' => ['type' => 'string', 'rules' => ['required', 'max:120']],
            'amount' => ['type' => 'number', 'rules' => ['required', 'numeric']],
        ]);
        $this->assertSame(['required', 'max:120', 'string'], (new SchemaCompiler)->laravelRules($schema)['value.title']);
    }
}
