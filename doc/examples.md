# Examples

Per-resource recipes. Domain map: [index.md](index.md). Error semantics: [errors.md](errors.md).

## Addresses

```php
// Create (updates when 'id' is included). Only the required keys are
// validated client-side; extra keys pass through untouched.
$saved = $client->address()->save([
    'address_type_id' => '1', // 1 = origin, 2 = destination
    'full_name'       => 'Cesar Alexis Fajardo Flores',
    'email'           => 'cesar@example.com',
    'telephone'       => '4921952109',
    'street'          => 'Fransico Zarco',
    'ext_number'      => '940',
    'int_number'      => '',
    'zip_code'        => '64000',
    'suburb'          => 'Monterrey Centro',
    'municipality'    => 'Monterrey',
    'town'            => 'Monterrey',
    'state'           => 'Nuevo León',
    'state_code'      => 'NL',
    'country_code'    => 'MX',
    'reference'       => 'Casa blanca12',
    'default_addr'    => 'false',
]);

echo $saved->getAddress()->getId();

// List, fetch, filter by type, delete (deletes are GETs on this API).
// List and single-fetch return native arrays of Address models.
$all       = $client->address()->all();
$one       = $client->address()->show(17638);
$origins   = $client->address()->byType(1);
$deleted   = $client->address()->delete(1761555);
```

## Packages

```php
$saved = $client->package()->save([
    'name'              => 'caja chica',
    'product_type'      => '47131900',
    'unit_type'         => 'XBX',
    'package_content'   => 'PLAYERAS',
    'amount_pkg'        => '150',
    'height'            => 13,
    'width'             => 11,
    'length'            => 10,
    'weight'            => 1,
    'real_weight'       => '1.00',
    'volumetric_weight' => '0.22',
    'bill_weight'       => '1.00',
    'default_pkg'       => 'false',
]);

echo $saved->getPackage()->getId();

$all = $client->package()->all();     // list<Package>
$one = $client->package()->show(502); // list<Package>
$client->package()->delete(501); // GET, like addresses
```

## Carriers & Services

```php
foreach ($client->parcel()->carriers() as $carrier) {
    echo "{$carrier->getTradeName()} (provider {$carrier->getProviderId()})" . PHP_EOL;
}

foreach ($client->parcel()->services() as $service) {
    echo "{$service->getParcel()} — {$service->getProviderId()}" . PHP_EOL;
    foreach ($service->getServices() as $row) {
        echo "  {$row->getLabel()} (max {$row->getMaxWeight()} kg)" . PHP_EOL;
    }
}
```

## Quotes

One URL, three modes — one method per mode so call sites cannot mix them up.
The package object must carry the weight keys or the API answers HTTP 200
with `error: true`.

```php
$quote = [
    'shipping_type' => '1',
    'quantity'      => 1,
    'origin'        => [/* address shape, e.g. zip 64000 */],
    'destination'   => [/* address shape, e.g. zip 03300 */],
    'package'       => [
        'product_type' => '01010101', 'unit_type' => 'X1A',
        'package_content' => 'qqq', 'amount_pkg' => '20000',
        'height' => 10, 'width' => 10, 'length' => 10, 'weight' => 1,
        'real_weight' => '1.00', 'volumetric_weight' => '1',
        'bill_weight' => '1', 'default_pkg' => '0',
    ],
];

// One carrier service (e.g. 11 = ESTAFETA Aéreo)
$byService = $client->quote()->byService($quote, providerServiceId: 11);

// One carrier (e.g. 9 = ESTAFETA)
$byProvider = $client->quote()->byProvider($quote, providerId: 9);

// All carriers
$all = $client->quote()->all($quote);

foreach ($all->getRates() as $rate) {
    $base = $rate->getCharges()[0] ?? null;
    echo "{$rate->getServiceName()}: " . ($base?->getTotal() ?? 0.0) . " MXN, ETA {$rate->getEstimatedDate()}" . PHP_EOL;
}

// Keep the transaction UUID — creating the order needs it
$uuid = $all->getTransactionUuid();
```

## Orders

```php
// Create from a quote UUID
$order = $client->order()->create(
    uuid: $uuid,
    providerId: '9',
    serviceId: '11',
    insurance: false,
);

foreach ($order->getGuides() as $guide) {
    echo $guide->getTrackingId() . PHP_EOL;
}

// History, single order, filtered history, transacting users
$history = $client->order()->all();
$one     = $client->order()->show('162199');
$filtered = $client->order()->filter([
    'date_range' => ['start' => '2024-03-28', 'end' => '2024-03-30'],
    'provider'   => 'all',
    'status'     => 'all',
    'user_id'    => 'all',
    'recipient'  => null,
]);
$users = $client->order()->transactions();

// Cancel (refund lands ~7 days later — not synchronous)
$client->order()->cancel(
    ['300000000011270006IT89'],
    '2025-02-27T17:23:42',
    '2025-03-04T17:23:42',
    '7',
    'Esta guía se generó como prueba y no se pretende utilizar',
    'rates',
);
```

## Labels (Guides)

```php
// Resolve the downloadable file id, then fetch binaries (base64 PDFs)
$file = $client->guide()->download([2055848]);
echo $file->getFileId();

$binaries = $client->guide()->binaries([1309085]);

foreach ($binaries->getFiles() as $pdf) {
    file_put_contents($pdf->getName(), base64_decode($pdf->getBinary()));
}

foreach ($binaries->getInvalidFiles() as $invalid) {
    echo "Failed: {$invalid->getGuideId()}" . PHP_EOL;
}
```

## Pickups

```php
// Pickup-eligible orders for given carriers
$eligible = $client->pickup()->ordersForPickup(['1']);

// All guides in one pickup must share the same origin — validated client-side
$client->pickup()->create([
    'who_delivers'          => 'Oswaldo Vazquez',
    'contact_phone'         => '8888888888',
    'contact_email'         => 'oswaldo@example.com',
    'data'                  => [[
        'trx_id'         => '426855',
        'origin_address' => 'Fransico Zarco, 940, Monterrey Centro, Monterrey, Nuevo León, 64000, MX',
    ]],
    'pickup_date'           => '2023-07-30 06:13 pm',
    'pickup_place'          => 2,
    'pickup_place_data'     => 'OFICINA',
    'origin'                => 'Fransico Zarco, 940, Monterrey Centro, Monterrey, Nuevo León, 64000, MX',
    'provider_id'           => '1',
    'description_reference' => 'prueba',
]);

$client->pickup()->cancel(id: 29114, description: 'No utilizaré la guía', userId: '1');
```

## Catalogs

```php
$productTypes = $client->catalog()->productTypes(); // get_catalog/pts
$packageTypes = $client->catalog()->packageTypes(); // get_catalog/pkt

foreach ($productTypes->getItems() as $item) {
    echo "{$item['key']}: {$item['value']}" . PHP_EOL;
}
```
