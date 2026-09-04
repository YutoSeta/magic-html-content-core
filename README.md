# Magic HTML Content Core

Framework-independent schema definition and record-value validation primitives for API-first content services. It does not know about Laravel, databases, HTTP, media storage, or domain-specific document types.

## 1.x の互換性について

1.x は消費者が content-platform-service のみの調整期で、minor でも受理条件が変わり得ます。
v1.3.0: スキーマ由来の validation rule は副作用のない許可リスト（SchemaDefinition::ALLOWED_RULES）に限定されました。exists / unique / active_url / ファイル系は拒否されます。
