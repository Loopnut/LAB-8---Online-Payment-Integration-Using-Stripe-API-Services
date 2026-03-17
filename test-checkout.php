<?php

require_once 'vendor/autoload.php';
$config = require 'config.php';
$client = new \GuzzleHttp\Client();

try {
    $response = $client->get('https://api.stripe.com/v1/products', [
        'auth' => [$config['secret_key'], ''],
        'query' => ['limit' => 1]
    ]);
    
    $products = json_decode($response->getBody(), true);
    
    if (!empty($products['data'])) {
        $product = $products['data'][0];
        echo "Product ID: " . $product['id'] . PHP_EOL;
        echo "Product Name: " . $product['name'] . PHP_EOL;
        
        // Test the checkout URL
        $checkoutUrl = 'checkout.php?product_id=' . $product['id'];
        echo "Checkout URL: " . $checkoutUrl . PHP_EOL;
    } else {
        echo "No products found" . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
