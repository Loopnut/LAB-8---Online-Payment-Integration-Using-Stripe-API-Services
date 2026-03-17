<?php

require_once 'vendor/autoload.php';

// Load configuration
$config = require 'config.php';
$secretKey = $config['secret_key'];

// Get product ID from query parameter
$productId = isset($_GET['product_id']) ? $_GET['product_id'] : null;

if (!$productId) {
    die('Product ID is required');
}

// Create HTTP client
$client = new \GuzzleHttp\Client();

try {
    // Fetch the product details to get its prices
    $productResponse = $client->get('https://api.stripe.com/v1/products/' . $productId, [
        'auth' => [$secretKey, '']
    ]);
    
    $product = json_decode($productResponse->getBody(), true);
    
    // Fetch prices for this product
    $pricesResponse = $client->get('https://api.stripe.com/v1/prices', [
        'auth' => [$secretKey, ''],
        'query' => [
            'product' => $productId,
            'limit' => 1
        ]
    ]);
    
    $prices = json_decode($pricesResponse->getBody(), true);
    
    if (empty($prices['data'])) {
        die('No prices found for this product');
    }
    
    $priceId = $prices['data'][0]['id'];
    
    // Define success and cancel URLs
    $yourDomain = 'http://localhost/stripe-php-app'; // Change this to your actual domain
    $successUrl = $yourDomain . '/success.php?session_id={CHECKOUT_SESSION_ID}';
    $cancelUrl = $yourDomain . '/cancel.php';
    
    // Create Checkout Session
    $sessionResponse = $client->post('https://api.stripe.com/v1/checkout/sessions', [
        'auth' => [$secretKey, ''],
        'form_params' => [
            'line_items[0][price]' => $priceId,
            'line_items[0][quantity]' => 1,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl
        ]
    ]);
    
    $session = json_decode($sessionResponse->getBody(), true);
    
    // Redirect to Stripe Checkout page
    header('Location: ' . $session['url']);
    exit;
    
} catch (\Exception $e) {
    die('Error creating checkout session: ' . $e->getMessage());
}
