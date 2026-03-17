<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Successful</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 500px;
        }
        h1 {
            color: #28a745;
            margin-bottom: 20px;
        }
        p {
            color: #666;
            line-height: 1.6;
            margin: 15px 0;
        }
        .session-id {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            word-break: break-all;
            font-family: monospace;
            font-size: 12px;
            margin: 20px 0;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        a:hover {
            background: #764ba2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✓ Payment Successful!</h1>
        <p>Thank you for your purchase. Your order has been confirmed.</p>
        
        <?php
        if (isset($_GET['session_id'])) {
            echo '<div class="session-id"><strong>Session ID:</strong><br>' . htmlspecialchars($_GET['session_id']) . '</div>';
        }
        ?>
        
        <p>You will receive an email confirmation shortly with your order details.</p>
        <a href="/stripe-php-app/">Return to Home</a>
    </div>
</body>
</html>