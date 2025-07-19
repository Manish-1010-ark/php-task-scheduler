<?php
require_once 'functions.php';

$message = '';
$status = 'error';

// Handle email verification from GET parameters
if (isset($_GET['email']) && isset($_GET['code'])) {
    $email = urldecode($_GET['email']);
    $code = $_GET['code'];
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } else {
        // Verify the subscription using the function from functions.php
        if (verifySubscription($email, $code)) {
            $message = "Subscription verified successfully for " . htmlspecialchars($email) . "!";
            $status = 'success';
        } else {
            $message = "Invalid verification code or email. The link may have expired or already been used.";
        }
    }
} else {
    $message = "Missing email or verification code. Please use the link from your verification email.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Task Scheduler</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-alignment: center;
        }
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .message {
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
            font-size: 16px;
            line-height: 1.5;
        }
        .message.success {
            background-color: #d4edda;
            border: 2px solid #c3e6cb;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            border: 2px solid #f5c6cb;
            color: #721c24;
        }
        .success-icon {
            font-size: 48px;
            color: #28a745;
            margin-bottom: 15px;
        }
        .error-icon {
            font-size: 48px;
            color: #dc3545;
            margin-bottom: 15px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .back-link:hover {
            background-color: #0056b3;
        }
        .instructions {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-size: 14px;
            color: #6c757d;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Do not modify the ID of the heading -->
        <h2 id="verification-heading">Subscription Verification</h2>
        
        <div class="message <?php echo $status; ?>">
                   
            <?php echo $message; ?>
        </div>
        
        <div style="text-align: center;">
            <a href="index.php" class="back-link">← Back to Task Scheduler</a>
        </div>
    </div>
</body>
</html>