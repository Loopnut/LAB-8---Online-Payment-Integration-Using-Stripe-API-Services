<?php

require_once 'vendor/autoload.php';


$config = require 'config.php';
$secretKey = $config['secret_key'];


$client = new \GuzzleHttp\Client();

try {
    
    $response = $client->get('https://api.stripe.com/v1/products', [
        'auth' => [$secretKey, ''],
        'query' => [
            'limit' => 100
        ]
    ]);
    
    
    $products = json_decode($response->getBody(), true);
    
    echo "Successfully fetched " . count($products['data']) . " products from Stripe.\n";
    
} catch (\Exception $e) {
    echo "Error fetching products: " . $e->getMessage() . "\n";
    $products = null;
}


