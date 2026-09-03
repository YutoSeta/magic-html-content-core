<?php

declare(strict_types=1);

namespace MagicHtml\Content;

final class SchemaValidator
{
    public function validate(SchemaDefinition $schema, mixed $value): array
    {
        $errors = [];
        if (! is_array($value)) {
            return ['value' => 'Record value must be an object.'];
        }

        foreach ($schema->fields as $name => $definition) {
            if (! is_string($name) || ! is_array($definition)) {
                $errors['schema'][] = 'Each field definition must be an object.';
                continue;
            }
            $exists = array_key_exists($name, $value);
            if (($definition['required'] ?? false) === true && (! $exists || $value[$name] === null || $value[$name] === '')) {
                $errors["value.{$name}"] = 'This field is required.';
                continue;
            }
            if (! $exists || $value[$name] === null) {
                continue;
            }
            $type = $definition['type'] ?? 'string';
            $valid = match ($type) {
                'string', 'text' => is_string($value[$name]),
                'number' => is_int($value[$name]) || is_float($value[$name]),
                'boolean' => is_bool($value[$name]),
                'array' => is_array($value[$name]) && array_is_list($value[$name]),
                'object', 'media', 'reference' => is_array($value[$name]),
                'select' => is_string($value[$name]) && in_array($value[$name], $definition['options'] ?? [], true),
                default => false,
            };
            if (! $valid) {
                $errors["value.{$name}"] = "Value does not match type '{$type}'.";
            }
        }

        return $errors;
    }
}
