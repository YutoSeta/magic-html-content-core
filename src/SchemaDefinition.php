<?php

declare(strict_types=1);

namespace MagicHtml\Content;

use InvalidArgumentException;

final readonly class SchemaDefinition
{
    /**
     * スキーマが持ち込める validation rule の許可リスト（rule 名の完全一致・whitelist が正）。
     *
     * 副作用のない rule だけを載せる。DB を叩く exists/unique、ネットワークに出る
     * active_url、ファイル/アップロード系（file/image/mimes/dimensions 等）、認証系
     * （current_password）は、スキーマ経由で Data Plane に side effect を注入できて
     * しまうため意図的に不掲載。regex/not_regex は許可するが、ReDoS の残余リスクは
     * 呼び出し側の実行時間制限に委ねる。
     */
    private const ALLOWED_RULES = [
        // presence / nullability
        'required', 'nullable', 'sometimes', 'filled', 'present', 'prohibited',
        'required_if', 'required_unless', 'required_with', 'required_with_all',
        'required_without', 'required_without_all', 'prohibited_if', 'prohibited_unless',
        // types
        'string', 'numeric', 'integer', 'boolean', 'array', 'json', 'decimal', 'list',
        // size / range
        'min', 'max', 'size', 'between', 'digits', 'digits_between', 'min_digits', 'max_digits',
        'gt', 'gte', 'lt', 'lte', 'multiple_of',
        // string formats（active_url はネットワークI/Oのため不可。url は構文検査のみなので可）
        'email', 'url', 'uuid', 'ulid', 'alpha', 'alpha_num', 'alpha_dash', 'ascii',
        'lowercase', 'uppercase', 'hex_color', 'ip', 'ipv4', 'ipv6', 'mac_address',
        'regex', 'not_regex', 'starts_with', 'ends_with', 'doesnt_start_with', 'doesnt_end_with',
        'in', 'not_in', 'distinct',
        // date
        'date', 'date_format', 'date_equals', 'after', 'before', 'after_or_equal', 'before_or_equal', 'timezone',
        // field comparison
        'same', 'different', 'confirmed', 'accepted', 'accepted_if', 'declined', 'declined_if',
    ];

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
            $this->assertRulesAllowed($name, $definition['rules'] ?? []);
        }
    }

    /**
     * スキーマ由来の rule は「許可リストに載った rule 名の文字列」だけを受理する。
     * JSON で運ばれるスキーマに object rule は存在し得ないため、文字列以外も拒否する。
     */
    private function assertRulesAllowed(string $field, array $rules): void
    {
        foreach ($rules as $rule) {
            if (! is_string($rule)) {
                throw new InvalidArgumentException("Field '{$field}': validation rules must be strings.");
            }
            $ruleName = strtolower(explode(':', $rule, 2)[0]);
            if (! in_array($ruleName, self::ALLOWED_RULES, true)) {
                throw new InvalidArgumentException("Field '{$field}': validation rule '{$ruleName}' is not allowed.");
            }
        }
    }

    public function toArray(): array
    {
        return ['slug' => $this->slug, 'display_name' => $this->displayName, 'fields' => $this->fields];
    }
}
