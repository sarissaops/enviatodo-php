# Testing

TDD is mandatory: Red → Green → Refactor. Suites: `unit`
(`tests/Api`, `tests/Model`, `tests/HttpClient`, `tests/Exception`) and
`integration-readonly` (`tests/Integration`).

## Sandbox runs without hardcoding tokens

Copy `.env.example` to `.env` and set `ENVIATODO_TOKEN` (sandbox or
production — the SDK treats both identically). The PHPUnit bootstrap loads
`.env` via `vlucas/phpdotenv` (require-dev only); real environment variables
always win, and missing `.env` simply skips the sandbox tests. Never commit
`.env` or real tokens.

```bash
cp .env.example .env
# edit .env, set ENVIATODO_TOKEN
vendor/bin/phpunit --group integration-readonly
```

## Groups

- Default run: unit only (mocked HTTP, hermetic, CI-safe).
- `--group integration-readonly`: read-only sandbox calls (quotes, lists, filters). Skipped without a token.
- `--group mutating`: sandbox writes (addresses, orders, pickups, deletes). Never in the default run; each needs explicit opt-in.

## Gates (every change)

```bash
composer validate
vendor/bin/phpunit
vendor/bin/phpstan analyse --level=max src tests
vendor/bin/php-cs-fixer fix --dry-run --diff
```

## Backends

Unit tests inject mocked PSR-18 clients. Integration runs resolve the backend
via discovery (`symfony/http-client` in require-dev); consumers may inject any
PSR-18 implementation (e.g. Guzzle) with identical outcomes.
