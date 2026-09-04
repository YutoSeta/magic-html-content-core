<?php

declare(strict_types=1);

namespace MagicHtml\Content;

final class SchemaCompiler
{
    public function jsonSchema(SchemaDefinition $schema): array
    {
        $properties = [];
        $required = [];
        foreach ($schema->fields as $name => $field) {
            $properties[$name] = $this->fieldSchema($field);
            if (($field['required'] ?? false) === true) $required[] = $name;
        }
        return ['$schema' => 'https://json-schema.org/draft/2020-12/schema', 'title' => $schema->displayName, 'type' => 'object', 'additionalProperties' => false, 'properties' => $properties, ...($required === [] ? [] : ['required' => $required])];
    }

    public function openApiSchema(SchemaDefinition $schema): array
    {
        $json = $this->jsonSchema($schema);
        unset($json['$schema']);
        return $json;
    }

    /** @return array<string, list<string>> */
    public function laravelRules(SchemaDefinition $schema, string $prefix = 'value'): array
    {
        $rules = [];
        foreach ($schema->fields as $name => $field) {
            $rules[$prefix.'.'.$name] = array_merge([($field['required'] ?? false) ? 'required' : 'nullable'], $this->constraints($field));
        }
        return $rules;
    }

    private function fieldSchema(array $field): array
    {
        $type = $field['type'] ?? 'string';
        $schema = match ($type) {
            'string', 'text', 'select' => ['type' => 'string'],
            'number' => ['type' => 'number'],
            'boolean' => ['type' => 'boolean'],
            'array' => ['type' => 'array'],
            default => ['type' => 'object'],
        };
        $constraints = $field['constraints'] ?? [];
        foreach (['maxLength' => 'maxLength', 'minLength' => 'minLength', 'pattern' => 'pattern', 'format' => 'format', 'minimum' => 'minimum', 'maximum' => 'maximum', 'minItems' => 'minItems', 'maxItems' => 'maxItems'] as $key => $output) if (array_key_exists($key, $constraints)) $schema[$output] = $constraints[$key];
        if ($type === 'select') $schema['enum'] = $field['options'] ?? [];
        return $schema;
    }

    private function constraints(array $field): array
    {
        $type = $field['type'] ?? 'string'; $c = $field['constraints'] ?? [];
        $rules = match ($type) { 'string', 'text', 'select' => ['string'], 'number' => ['numeric'], 'boolean' => ['boolean'], 'array' => ['array'], 'object', 'media', 'reference' => ['array'], default => [] };
        if ($type === 'select') $rules[] = 'in:'.implode(',', $field['options'] ?? []);
        foreach (['minLength' => 'min:', 'maxLength' => 'max:', 'pattern' => 'regex:', 'format' => null, 'minimum' => 'min:', 'maximum' => 'max:', 'minItems' => 'min:', 'maxItems' => 'max:'] as $key => $rule) {
            if (array_key_exists($key, $c) && $rule !== null) $rules[] = $rule.$c[$key];
        }
        if (($c['format'] ?? null) === 'email') $rules[] = 'email';
        return $rules;
    }
}
