# Event Schema Versioning Policy

This service uses `schema_version` in every inbound/outbound event envelope.

Rules:
- **Backward compatible changes** (add optional fields) -> increment **minor** schema version (e.g. 1 → 2) only if consumers depend on it.
- **Breaking changes** (remove/rename fields or change semantics) -> increment **major** schema version and support both versions in consumers until deprecation window ends.
- **Consumers** must reject unsupported `schema_version` with a non-retryable error.
- **Producers** must not remove fields without deprecation notice and documented migration.

Current supported inbound schema versions:
- `config('rabbitmq.inbound.schema_versions')` (default: `1`)

Deprecation:
- Any schema version removal must be announced and supported for at least one release cycle.
