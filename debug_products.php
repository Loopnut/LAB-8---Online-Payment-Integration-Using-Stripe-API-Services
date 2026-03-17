<?php

require_once 'vendor/autoload.php';

$config = require 'config.php';
$client = new \GuzzleHttp\Client();

$response = $client->get('https://api.stripe.com/v1/products', [
    'auth' => [$config['secret_key'], ''],
    'query' => [
        'limit' => 100,
        'active' => 'true'
    ]
]);

$data = json_decode($response->getBody(), true);

foreach ($data['data'] as $p) {
    echo $p['id'] . ' | ' . $p['name'] . "\n";
}

echo "count: " . count($data['data']) . "\n";
