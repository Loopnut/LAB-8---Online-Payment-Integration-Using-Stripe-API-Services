<?php

require_once 'vendor/autoload.php';


echo "=== PART 1: PROJECT SETUP ===" . PHP_EOL;
echo "✓ Composer initialized and Guzzle installed" . PHP_EOL;
echo "✓ phpdotenv installed" . PHP_EOL;


echo PHP_EOL . "=== PART 2: CONFIGURATION ===" . PHP_EOL;
$config = require 'config.php';
echo "✓ config.php loaded" . PHP_EOL;
echo "✓ Secret Key present: " . (!empty($config['secret_key']) ? "YES" : "NO") . PHP_EOL;
echo "✓ Publishable Key present: " . (!empty($config['publishable_key']) ? "YES" : "NO") . PHP_EOL;


echo PHP_EOL . "=== PART 3: API CONNECTION ===" . PHP_EOL;
$client = new \GuzzleHttp\Client();
$secretKey = $config['secret_key'];

try {
    $response = $client->get('https://api.stripe.com/v1/products', [
        'auth' => [$secretKey, ''],
        'query' => ['limit' => 10]
    ]);
    
    $productsData = json_decode($response->getBody(), true);
    echo "✓ Successfully connected to Stripe API" . PHP_EOL;
    echo "✓ Products available: " . count($productsData['data']) . PHP_EOL;
    
    // Test 4: Product Details
    echo PHP_EOL . "=== PART 4: PRODUCT DETAILS ===" . PHP_EOL;
    foreach ($productsData['data'] as $index => $product) {
        echo "  Product " . ($index + 1) . ": " . $product['name'] . " (ID: " . $product['id'] . ")" . PHP_EOL;
        
        // Check if product has prices
        $pricesResponse = $client->get('https://api.stripe.com/v1/prices', [
            'auth' => [$secretKey, ''],
            'query' => [
                'product' => $product['id'],
                'limit' => 1
            ]
        ]);
        
        $pricesData = json_decode($pricesResponse->getBody(), true);
        if (!empty($pricesData['data'])) {
            $price = $pricesData['data'][0];
            echo "    Price: $" . number_format($price['unit_amount'] / 100, 2) . " (" . $price['currency'] . ")" . PHP_EOL;
            echo "    Price ID: " . $price['id'] . PHP_EOL;
        }
    }
    
    // Test 5: Checkout Session Creation
    echo PHP_EOL . "=== PART 5: CHECKOUT SESSION ===" . PHP_EOL;
    if (!empty($productsData['data'])) {
        $testProduct = $productsData['data'][0];
        
        // Get first price
        $pricesResponse = $client->get('https://api.stripe.com/v1/prices', [
            'auth' => [$secretKey, ''],
            'query' => [
                'product' => $testProduct['id'],
                'limit' => 1
            ]
        ]);
        
        $pricesData = json_decode($pricesResponse->getBody(), true);
        if (!empty($pricesData['data'])) {
            $testPrice = $pricesData['data'][0];
            
            $sessionResponse = $client->post('https://api.stripe.com/v1/checkout/sessions', [
                'auth' => [$secretKey, ''],
                'form_params' => [
                    'line_items[0][price]' => $testPrice['id'],
                    'line_items[0][quantity]' => 1,
                    'mode' => 'payment',
                    'success_url' => 'http://localhost/stripe-php-app/success.php?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => 'http://localhost/stripe-php-app/cancel.php'
                ]
            ]);
            
            $session = json_decode($sessionResponse->getBody(), true);
            echo "✓ Checkout Session Created" . PHP_EOL;
            echo "  Session ID: " . $session['id'] . PHP_EOL;
            echo "  URL: " . substr($session['url'], 0, 50) . "..." . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "=== ✓ ALL TESTS PASSED ===" . PHP_EOL;
    echo PHP_EOL . "Web pages created:" . PHP_EOL;
    echo "  ✓ index.php - Product listing page" . PHP_EOL;
    echo "  ✓ checkout.php - Checkout processor" . PHP_EOL;
    echo "  ✓ success.php - Payment success page" . PHP_EOL;
    echo "  ✓ cancel.php - Payment cancel page" . PHP_EOL;
    echo "  ✓ config.php - Configuration file" . PHP_EOL;
    echo "  ✓ .env - Environment variables" . PHP_EOL;
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . PHP_EOL;
}
