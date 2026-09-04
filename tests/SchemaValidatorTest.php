<?php

namespace MagicHtml\Content\Tests;

use MagicHtml\Content\SchemaDefinition;
use MagicHtml\Content\SchemaValidator;
use MagicHtml\Content\SchemaCompiler;
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

    public function test_it_compiles_one_schema_to_json_schema_openapi_and_laravel_rules(): void
    {
        $schema = new SchemaDefinition('article', 'Article', ['title' => ['type' => 'string', 'required' => true, 'constraints' => ['maxLength' => 120]], 'email' => ['type' => 'string', 'constraints' => ['format' => 'email']]]);
        $compiler = new SchemaCompiler;
        $this->assertSame('object', $compiler->jsonSchema($schema)['type']);
        $this->assertSame(120, $compiler->openApiSchema($schema)['properties']['title']['maxLength']);
        $this->assertSame(['required', 'string', 'max:120'], $compiler->laravelRules($schema)['value.title']);
    }
}
