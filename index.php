<?php

require_once 'vendor/autoload.php';

$config = require 'config.php';
$secretKey = $config['secret_key'];
$publishableKey = $config['publishable_key'];

$client = new \GuzzleHttp\Client();

$products = [];
$error = null;

try {
    
    $response = $client->get('https://api.stripe.com/v1/products', [
        'auth' => [$secretKey, ''],
        'query' => [
            'limit' => 100,
            'active' => 'true'
        ]
    ]);
    
    $productsData = json_decode($response->getBody(), true);
    $products = $productsData['data'] ?? [];
    
    // Fetch prices and images for each product
    foreach ($products as &$product) {
        $pricesResponse = $client->get('https://api.stripe.com/v1/prices', [
            'auth' => [$secretKey, ''],
            'query' => [
                'product' => $product['id'],
                'limit' => 1,
                'active' => 'true'
            ]
        ]);
        
        $pricesData = json_decode($pricesResponse->getBody(), true);
        $product['price'] = !empty($pricesData['data']) ? $pricesData['data'][0] : null;
        
        // Set default image if product has images
        $product['image'] = isset($product['images']) && !empty($product['images']) 
            ? $product['images'][0] 
            : null;
    }
    unset($product);
    
} catch (\Exception $e) {
    $error = "Error fetching products: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>BM Market</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', sans-serif;
            background: radial-gradient(circle at top, #2b2b2b 0%, #0f0f0f 60%);
            color: #e5e5e5;
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        header {
            text-align: center;
            margin-bottom: 50px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            padding-bottom: 35px;
        }
        
        header h1 {
            font-size: 3.2em;
            font-weight: 200;
            letter-spacing: 3px;
            color: #ffffff;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        
        header p {
            font-size: 0.9em;
            color: rgba(255, 255, 255, 0.65);
            letter-spacing: 1.2px;
            text-transform: uppercase;
            font-weight: 300;
        }
        
        .error {
            background: rgba(255, 60, 60, 0.12);
            border: 1px solid rgba(255, 60, 60, 0.4);
            color: #ffb3b3;
            padding: 18px;
            margin-bottom: 30px;
            border-radius: 6px;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }
        
        .product-card {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            overflow: hidden;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        
        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 22px 38px rgba(0, 0, 0, 0.45);
            border-color: rgba(255, 255, 255, 0.18);
        }
        
        .product-image {
            width: 100%;
            height: 260px;
            background: rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: saturate(0.92) brightness(0.92);
            transition: transform 0.3s ease, filter 0.3s ease;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.04);
            filter: saturate(1) brightness(1);
        }
        
        .product-image-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.04) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.4);
            font-size: 3em;
        }
        
        .product-info {
            padding: 22px;
        }
        
        .product-name {
            font-size: 1.1em;
            font-weight: 500;
            margin-bottom: 8px;
            color: #ffffff;
            letter-spacing: 0.8px;
        }
        
        .product-description {
            color: rgba(255, 255, 255, 0.65);
            font-size: 0.85em;
            margin-bottom: 14px;
            line-height: 1.5;
            min-height: 34px;
        }
        
        .product-price {
            font-size: 1.6em;
            font-weight: 500;
            color: #ffffff;
            margin-bottom: 18px;
            letter-spacing: 0.8px;
        }
        
        .buy-button {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.25);
            font-size: 0.9em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            letter-spacing: 0.8px;
            border-radius: 5px;
        }
        
        .buy-button:hover {
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 255, 255, 0.4);
        }
        
        .empty-state {
            background: rgba(255, 255, 255, 0.06);
            padding: 60px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            border-radius: 10px;
        }
        
        .empty-state h2 {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 10px;
            font-weight: 400;
            letter-spacing: 1.5px;
        }
        
        .empty-state p {
            color: rgba(255, 255, 255, 0.55);
            font-weight: 300;
        }
        
        footer {
            text-align: center;
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.85em;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 30px;
            letter-spacing: 0.5px;
            font-weight: 300;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Black Market</h1>
            <p>Premium Collection</p>
        </header>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <h2>No Products Available</h2>
                <p>The catalog is currently empty.</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($product['image']): ?>
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <div class="product-image-placeholder">
                                    ◆
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                            
                            <?php if (!empty($product['description'])): ?>
                                <div class="product-description">
                                    <?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($product['price']): ?>
                                <div class="product-price">
                                    $ <?php echo number_format($product['price']['unit_amount'] / 100, 2); ?>
                                </div>
                            <?php endif; ?>
                            
                            <a href="checkout.php?product_id=<?php echo htmlspecialchars($product['id']); ?>" class="buy-button">
                                Aquire
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <footer>
            Secure Checkout • Test Mode
        </footer>
    </div>
</body>
</html>