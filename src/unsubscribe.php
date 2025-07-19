<?php
require_once 'functions.php';

$status = '';
$message = '';

// Handle unsubscribe logic
if (isset($_GET['email'])) {
    $email = urldecode($_GET['email']);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (unsubscribeEmail($email)) {
            $status = 'success';
            $message = 'You have been unsubscribed successfully.';
        } else {
            $status = 'error';
            $message = 'Email not found or already unsubscribed.';
        }
    } else {
        $status = 'error';
        $message = 'Invalid email address.';
    }
} else {
    $status = 'error';
    $message = 'Invalid request: email not provided.';
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Unsubscribe - Task Planner</title>
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
        <h1 class="page-title">Unsubscribe</h1>

        <div class="status-card <?= $status ?>">
            <div class="status-icon">
                <?php if ($status === 'success'): ?>
                    ✓
                <?php else: ?>
                    ✕
                <?php endif; ?>
            </div>
            <div class="status-message">
                <?= $message; ?>
            </div>
        </div>

        <?php if ($status === 'success'): ?>
            <div class="info-text">
                If this was a mistake, you may resubscribe at any time from the Task Planner homepage.
            </div>
        <?php else: ?>
            <div class="info-text">
                If you continue to have problems unsubscribing, please contact support or try using the unsubscribe link from your most recent reminder email.
            </div>
        <?php endif; ?>

        <a class="back-button" href="index.php">
            ← Back to Task Scheduler
        </a>
    </div>
</body>
</html>