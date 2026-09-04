<?php

namespace MagicHtml\Content\Tests;

use InvalidArgumentException;
use MagicHtml\Content\SchemaDefinition;
use PHPUnit\Framework\TestCase;

final class SchemaDefinitionRulesTest extends TestCase
{
    private function schema(array $rules): SchemaDefinition
    {
        return new SchemaDefinition('test-schema', 'テスト', [
            'field' => ['type' => 'string', 'rules' => $rules],
        ]);
    }

    public function test_許可リスト内のruleは受理する(): void
    {
        $schema = $this->schema(['required', 'string', 'max:120', 'min:1', 'regex:/^[a-z]+$/', 'in:a,b', 'email', 'date_format:Y-m-d']);

        $this->assertSame('test-schema', $schema->slug);
    }

    public function test_existsはDBを叩くため拒否する(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'exists' is not allowed");
        $this->schema(['required', 'exists:users,id']);
    }

    public function test_uniqueはDBを叩くため拒否する(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->schema(['unique:content_records,slug']);
    }

    public function test_active_urlはネットワークに出るため拒否する(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'active_url' is not allowed");
        $this->schema(['active_url']);
    }

    public function test_ファイル系ruleは拒否する(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->schema(['mimes:pdf']);
    }

    public function test_大文字表記でも同じ判定になる(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->schema(['Exists:users,id']);
    }

    public function test_文字列以外のruleは拒否する(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('rules must be strings');
        $this->schema([['not' => 'a string rule']]);
    }

    public function test_rules未指定のフィールドは従来どおり通る(): void
    {
        $schema = new SchemaDefinition('plain', 'プレーン', [
            'title' => ['type' => 'string', 'required' => true],
        ]);

        $this->assertSame('plain', $schema->slug);
    }
}
