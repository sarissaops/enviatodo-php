# Errors & envelope semantics

Every response is an envelope: `{success, message, data, error, code}`.
The SDK unwraps `data` for you and raises on failure — including failures
arriving with HTTP 200, which this API does regularly.

| Situation | Wire shape | SDK behavior |
|---|---|---|
| Success | `success:true, error:false` | Typed model / model list |
| Validation failure | HTTP 400 + `error:true` | `HttpClientException` (factory per status) |
| Failure inside HTTP 200 | `success:false` (with `error:true`, `error:false`, or `error:null`) | `EnviatodoException` with envelope message + numeric code |
| Auth failure | HTTP 403, `error:403`, `code:"Forbidden"` (fields swapped) | `HttpClientException::forbidden`, code falls back to 403 |
| Server failure | HTTP 5xx (sometimes an HTML error page) | `HttpServerException`, before hydration runs |
| Unknown ids | HTTP 200 + empty `data: []` (never 404) | Empty list |
| Deletes of missing rows | HTTP 200 + `success:false`, `data:null` | `EnviatodoException` |

## Exception hierarchy

All exceptions implement `SarissaOps\Enviatodo\Exception` — catch the marker
to handle every SDK failure in one place:

- `EnviatodoException` — the envelope itself reported an error.
- `HttpClientException` — HTTP 4xx (`getResponseCode()`, `getResponseBody()`, `getResponse()`).
- `HttpServerException` — HTTP 5xx + network failures (`networkError()` keeps the previous exception).
- `UnknownErrorException` — anything else.
- `HydrationException` — undecodable or non-JSON bodies.
- `InvalidArgumentException` — caller-side validation (empty/missing arguments, mixed pickup origins). Thrown before any HTTP traffic.

## API quirks the SDK encodes

- Deletes are `GET` requests, not DELETE.
- `add_package/` keeps its trailing slash.
- `rates_client` is one URL with three modes (`provider_service_id` / `provider_id` / neither).
- `get_address_by_type_id` also sends `x-enviatodo-client: 1`.
- Path ids are URL segments; unknown ids return empty lists, never 404.
- Loose typing throughout (numeric strings, string `"200"` codes, `error: null`) is normalized in models and hydrators.
