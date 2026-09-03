<?php

declare(strict_types=1);

namespace MagicHtml\Content;

use RuntimeException;

final class SchemaException extends RuntimeException
{
    public function __construct(public readonly array $errors)
    {
        parent::__construct('Value does not satisfy the content schema.');
    }
}
