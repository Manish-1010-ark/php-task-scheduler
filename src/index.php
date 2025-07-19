<?php
require_once 'functions.php';

// Initialize data files if they don't exist
if (!file_exists('tasks.txt')) {
    file_put_contents('tasks.txt', '[]');
}
if (!file_exists('subscribers.txt')) {
    file_put_contents('subscribers.txt', '[]');
}
if (!file_exists('pending_subscriptions.txt')) {
    file_put_contents('pending_subscriptions.txt', '{}');
}

$message = '';

// Handle email verification from GET parameters
if (isset($_GET['verify']) && isset($_GET['email']) && isset($_GET['code'])) {
    $email = urldecode($_GET['email']);
    $code = $_GET['code'];
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (verifySubscription($email, $code)) {
            $message = "Email verification successful! You will now receive task reminders.";
        } else {
            $message = "Invalid verification code or email already verified.";
        }
    } else {
        $message = "Invalid email format.";
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['task-name']) && !empty(trim($_POST['task-name']))) {
        $task_name = trim($_POST['task-name']);
        if (addTask($task_name)) {
            $message = "Task added successfully!";
        } else {
            $message = "Task already exists or could not be added.";
        }
    }
    
    if (isset($_POST['email']) && !empty(trim($_POST['email']))) {
        $email = trim($_POST['email']);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (subscribeEmail($email)) {
                $message = "Verification email sent! Please check your inbox.";
            } else {
                $message = "Email is already subscribed or could not be processed.";
            }
        } else {
            $message = "Please enter a valid email address.";
        }
    }
    
    // Handle task status updates
    if (isset($_POST['task_id']) && isset($_POST['action'])) {
        $task_id = $_POST['task_id'];
        $action = $_POST['action'];
        
        if ($action === 'toggle') {
            $tasks = getAllTasks();
            foreach ($tasks as $task) {
                if ($task['id'] === $task_id) {
                    markTaskAsCompleted($task_id, !$task['completed']);
                    break;
                }
            }
        } elseif ($action === 'delete') {
            deleteTask($task_id);
        }
    }
}

// Get all tasks for display
$tasks = getAllTasks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Scheduler</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        input[type="text"], input[type="email"] {
            width: 70%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .task-list {
            list-style: none;
            padding: 0;
        }
        .task-item {
            display: flex;
            align-items: center;
            padding: 10px;
            margin: 5px 0;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }
        .task-item.completed {
            background-color: #d4edda;
            text-decoration: line-through;
            opacity: 0.7;
        }
        .task-status {
            margin-right: 10px;
        }
        .task-name {
            flex-grow: 1;
            margin-right: 10px;
        }
        .delete-task {
            background-color: #dc3545;
            padding: 5px 10px;
            font-size: 12px;
        }
        .delete-task:hover {
            background-color: #c82333;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .message.success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .section {
            margin-bottom: 40px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-container {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Task Scheduler</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo (strpos($message, 'successfully') !== false || strpos($message, 'successful') !== false) ? 'success' : ((strpos($message, 'failed') !== false || strpos($message, 'Invalid') !== false) ? 'error' : ''); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Add Task Form -->
        <div class="form-container">
            <h2>Add New Task</h2>
            <form method="POST" action="">
                <input type="text" name="task-name" id="task-name" placeholder="Enter new task" required>
                <button type="submit" id="add-task">Add Task</button>
            </form>
        </div>

        <!-- Tasks List -->
        <div class="section">
            <h2>Tasks</h2>
            <ul class="task-list">
                <?php if (empty($tasks)): ?>
                    <li style="text-align: center; color: #666; padding: 20px;">
                        No tasks yet. Add your first task above!
                    </li>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <li class="task-item <?php echo $task['completed'] ? 'completed' : ''; ?>">
                            <form method="POST" style="display: flex; align-items: center; width: 100%;">
                                <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task['id']); ?>">
                                <input type="hidden" name="action" value="toggle">
                                <input type="checkbox" class="task-status" 
                                       <?php echo $task['completed'] ? 'checked' : ''; ?>
                                       onchange="this.form.submit()">
                                <span class="task-name"><?php echo htmlspecialchars($task['name']); ?></span>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task['id']); ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="delete-task" 
                                        onclick="return confirm('Are you sure you want to delete this task?')">Delete</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Email Subscription Form -->
        <div class="form-container">
            <h2>Subscribe for Task Reminders</h2>
            <p>Get hourly email reminders for pending tasks.</p>
            <form method="POST" action="">
                <input type="email" name="email" placeholder="Enter your email address" required />
                <button type="submit" id="submit-email">Subscribe</button>
            </form>
        </div>
    </div>

    <script>
        // Auto-submit form when checkbox is clicked
        document.querySelectorAll('.task-status').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                this.form.submit();
            });
        });
    </script>
</body>
</html>