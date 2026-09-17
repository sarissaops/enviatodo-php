# enviatodo-php — domain map

All 10 API domains, their methods, and the Enviatodo operations behind them.
Full recipes: [examples.md](examples.md). Error semantics: [errors.md](errors.md).

| Domain (facade) | Method | HTTP | Path |
|---|---|---|---|
| `balance()` | `show()` | GET | `Api/get_client_balance` |
| `zipCode()` | `show($zip)` | GET | `Api/get_zip_code/{zip}` |
| `address()` | `save($data)` | POST | `Api/add_address` |
| `address()` | `all()` | GET | `Api/get_address` |
| `address()` | `show($id)` | GET | `Api/get_address_by_id/{id}` (returns a list) |
| `address()` | `byType($type)` | GET | `Api/get_address_by_type_id/{type}` (+ `x-enviatodo-client: 1`) |
| `address()` | `delete($id)` | GET | `Api/delete_address_by_id/{id}` (deletes are GETs) |
| `package()` | `save($data)` | POST | `Api/add_package/` (trailing slash is significant) |
| `package()` | `all()` | GET | `Api/get_packages` |
| `package()` | `show($id)` | GET | `Api/get_package_by_id/{id}` (returns a list) |
| `package()` | `delete($id)` | GET | `Api/delete_package/{id}` (deletes are GETs) |
| `parcel()` | `carriers()` | GET | `Api/get_parcel_service` |
| `parcel()` | `services()` | GET | `Api/provider_services` |
| `quote()` | `byService($q, $serviceId)` | POST | `Api/rates_client` (one URL, three modes) |
| `quote()` | `byProvider($q, $providerId)` | POST | `Api/rates_client` |
| `quote()` | `all($q)` | POST | `Api/rates_client` |
| `order()` | `create($uuid, $providerId, $serviceId, $insurance)` | POST | `Api/create_order` (needs a quote UUID) |
| `order()` | `cancel(...)` | POST | `Api/cancel_order` (refund ~7 days, not synchronous) |
| `order()` | `all()` | GET | `Api/get_orders` |
| `order()` | `show($trxId)` | GET | `Api/get_order/{trx_id}` |
| `order()` | `filter($criteria)` | POST | `Api/get_orders_filter` (`recipient` always sent) |
| `order()` | `transactions()` | GET | `Api/user_transactions` |
| `guide()` | `download($ids)` | POST | `Api/download_guides` → `file_id` |
| `guide()` | `binaries($ids)` | POST | `Api/download_guide_binaries` → base64 `files[]` + `invalid_files[]` |
| `pickup()` | `ordersForPickup($providers)` | POST | `Api/get_orders_for_pickup_by_client_id` (`{"providers":[...]}`) |
| `pickup()` | `create($data)` | POST | `Api/add_pickup` (shared origin required) |
| `pickup()` | `cancel($id, $desc, $userId)` | POST | `Api/cancel_pickup` |
| `catalog()` | `productTypes()` | GET | `Api/get_catalog/pts` |
| `catalog()` | `packageTypes()` | GET | `Api/get_catalog/pkt` |
