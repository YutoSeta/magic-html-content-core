<?php

declare(strict_types=1);

namespace MagicHtml\Content;

final class SchemaCompiler
{
    /** @return array<string, list<string|object>> */
    public function laravelRules(SchemaDefinition $schema, string $prefix = 'value'): array
    {
        $rules = [];
        foreach ($schema->fields as $name => $field) $rules[$prefix.'.'.$name] = $this->fieldRules($field);
        return $rules;
    }

    /** @return list<string|object> */
    private function fieldRules(array $field): array
    {
        $rules = array_values($field['rules'] ?? []);
        $hasType = count(array_filter($rules, fn (mixed $rule): bool => is_string($rule) && preg_match('/^(string|numeric|boolean|array|integer|json)(:|$)/', $rule) === 1)) > 0;
        if (! $hasType) $rules[] = match ($field['type'] ?? 'string') { 'string', 'text', 'select' => 'string', 'number' => 'numeric', 'boolean' => 'boolean', 'array', 'media', 'reference', 'object' => 'array', default => 'string' };
        if (($field['type'] ?? null) === 'select' && ($field['options'] ?? []) !== [] && ! $this->hasRule($rules, 'in:')) $rules[] = 'in:'.implode(',', $field['options']);
        return $rules === [] ? ['nullable'] : $rules;
    }

    private function hasRule(array $rules, string $prefix): bool
    {
        return count(array_filter($rules, fn (mixed $rule): bool => is_string($rule) && str_starts_with($rule, $prefix))) > 0;
    }
}
