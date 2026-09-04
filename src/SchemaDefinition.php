<?php

declare(strict_types=1);

namespace MagicHtml\Content;

use InvalidArgumentException;

final readonly class SchemaDefinition
{
    public function __construct(
        public string $slug,
        public string $displayName,
        public array $fields,
    ) {
        if (! preg_match('/^[a-z][a-z0-9_-]{0,63}$/', $slug)) {
            throw new InvalidArgumentException('Schema slug must be lowercase kebab/snake-case.');
        }
        if ($displayName === '' || $fields === []) {
            throw new InvalidArgumentException('Schema display name and fields are required.');
        }
        foreach ($fields as $name => $definition) {
            if (! is_string($name) || ! preg_match('/^[a-z][a-z0-9_]{0,63}$/', $name) || ! is_array($definition)) {
                throw new InvalidArgumentException('Field names must be lowercase snake_case and definitions must be objects.');
            }
            if (! in_array($definition['type'] ?? 'string', ['string', 'text', 'number', 'boolean', 'array', 'object', 'media', 'reference', 'select'], true) || (isset($definition['rules']) && ! is_array($definition['rules']))) {
                throw new InvalidArgumentException("Unsupported field type for '{$name}'.");
            }
        }
    }

    public function toArray(): array
    {
        return ['slug' => $this->slug, 'display_name' => $this->displayName, 'fields' => $this->fields];
    }
}
