# Changelog

All notable changes to `sarissaops/enviatodo-php` are documented here, following
[Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [Unreleased]

### Added

- `HttpClientConfigurator::setTimeout()` / `getTimeout()` timeout contract for
  downstream bundles (enforcement is backend-specific; null means no preference).
- `RateRow::getCurrency()` (from `detail_charges`) and `Balance::getCurrency()`
  (`'MXN'`, the API reports no balance currency).
- `OrderStatus::tryFromLabel()` case/diacritic-insensitive status matching.
- Empty-token guard in `createConfiguredClient()` — fails fast instead of a
  confusing live 403.
- `HttpClientConfigurator::DEFAULT_ENDPOINT` single canonical sandbox default,
  referenced by `Enviatodo::create()`.

## [0.1.0] — 2026-09-15

Initial release: framework-agnostic PSR-18 client for the Enviatodo Shipping API (V2).

### Added

- `Enviatodo` facade (`Enviatodo::create($token, $endpoint)`, per-domain factories, `getLastResponse()`).
- 10 API classes covering all 29 documented operations: `ZipCodeApi`, `BalanceApi`, `AddressApi` (5 ops, GET-deletes, `x-enviatodo-client` header), `PackageApi` (4 ops, trailing-slash `add_package/`), `ParcelApi` (carriers + services), `QuoteApi` (3 modes, one URL), `OrderApi` (create/cancel/all/show/filter/transactions), `GuideApi` (file id + base64 binaries), `PickupApi` (eligible/create/cancel), `CatalogApi` (product/package types).
- Envelope-aware hydrators (`Model`/`Array`/`Noop`): unwrap `data`, throw on `error: true` even at HTTP 200, tolerate string `code` and null `data`.
- Typed exception hierarchy (`EnviatodoException`, `HttpClientException`, `HttpServerException`, `UnknownErrorException`, `HydrationException`, `InvalidArgumentException`) plus `Assert` proxy.
- Value-object models with nullable-tolerant getters; `OrderStatus`, `PickupStatus`, `MexicanState`, `CarrierId` enums.
- PHPUnit suite (unit + `integration-readonly` + `mutating` groups), PHPStan level max, php-cs-fixer (`@PER-CS`).
