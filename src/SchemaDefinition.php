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
    }

    public function toArray(): array
    {
        return ['slug' => $this->slug, 'display_name' => $this->displayName, 'fields' => $this->fields];
    }
}
