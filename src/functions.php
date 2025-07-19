<?php

/**
 * Adds a new task to the task list
 * 
 * @param string $task_name The name of the task to add.
 * @return bool True on success, false on failure.
 */
function addTask(string $task_name): bool {
    $file = __DIR__ . '/tasks.txt';
    $tasks = getAllTasks();
    
    // Check for duplicate task names
    foreach ($tasks as $task) {
        if (trim($task['name']) === trim($task_name)) {
            return false; 
        }
    }
    
    $task_id = uniqid();
    $new_task = [
        'id' => $task_id,
        'name' => $task_name,
        'completed' => false
    ];
    
    $tasks[] = $new_task;
    file_put_contents($file, json_encode($tasks));
    return true;
}

/**
 * Retrieves all tasks from the tasks.txt file
 * 
 * @return array Array of tasks. -- Format [ id, name, completed ]
 */
function getAllTasks(): array {
    $file = __DIR__ . '/tasks.txt';
    if (!file_exists($file)) {
        return [];
    }
    $tasks = file_get_contents($file);
    if (empty($tasks)) {
        return [];
    }
    return json_decode($tasks, true) ?: [];
}

/**
 * Marks a task as completed or uncompleted
 * 
 * @param string $task_id The ID of the task to mark.
 * @param bool $is_completed True to mark as completed, false to mark as uncompleted.
 * @return bool True on success, false on failure
 */
function markTaskAsCompleted(string $task_id, bool $is_completed): bool {
    $file = __DIR__ . '/tasks.txt';
    $task_data = getAllTasks();
    
    for ($i = 0; $i < count($task_data); $i++) {
        if ($task_data[$i]['id'] === $task_id) {
            $task_data[$i]['completed'] = $is_completed;
            file_put_contents($file, json_encode($task_data));
            return true;
        }
    }
    return false;
}

/**
 * Deletes a task from the task list
 * 
 * @param string $task_id The ID of the task to delete.
 * @return bool True on success, false on failure.
 */
function deleteTask(string $task_id): bool {
    $file = __DIR__ . '/tasks.txt';
    $task_data = getAllTasks();
    
    foreach ($task_data as $index => $task) {
        if ($task['id'] === $task_id) {
            unset($task_data[$index]);
            $task_data = array_values($task_data); // reindex
            file_put_contents($file, json_encode($task_data));
            return true;
        }
    }
    return false;
}

/**
 * Generates a 6-digit verification code
 * 
 * @return string The generated verification code.
 */
function generateVerificationCode(): string {
    $code = random_int(100000, 999999);
    return strval($code);
}

/**
 * Subscribe an email address to task notifications.
 *
 * Generates a verification code, stores the pending subscription,
 * and sends a verification email to the subscriber.
 *
 * @param string $email The email address to subscribe.
 * @return bool True if verification email sent successfully, false otherwise.
 */
function subscribeEmail(string $email): bool {
    $subscribersFile = __DIR__ . '/subscribers.txt';
    $pendingFile = __DIR__ . '/pending_subscriptions.txt';

    // Check if already subscribed - FIX: Handle empty/null JSON properly
    $subscribers = [];
    if (file_exists($subscribersFile)) {
        $subscribersContent = file_get_contents($subscribersFile);
        if (!empty($subscribersContent)) {
            $decodedSubscribers = json_decode($subscribersContent, true);
            $subscribers = is_array($decodedSubscribers) ? $decodedSubscribers : [];
        }
    }
    
    if (in_array($email, $subscribers)) {
        return false;
    }

    // Check if already pending verification - FIX: Handle empty/null JSON properly
    $pending = [];
    if (file_exists($pendingFile)) {
        $pendingContent = file_get_contents($pendingFile);
        if (!empty($pendingContent)) {
            $decodedPending = json_decode($pendingContent, true);
            $pending = is_array($decodedPending) ? $decodedPending : [];
        }
    }
    
    if (isset($pending[$email])) {
        return false;
    }

    $code = generateVerificationCode();
    $pending[$email] = [
        'code' => $code,
        'timestamp' => time()
    ];
    file_put_contents($pendingFile, json_encode($pending));

    // Create verification link - adjust URL as needed for your setup
    $base_url = "http://localhost:8000";
    $verification_link = $base_url . "/verify.php?email=" . urlencode($email) . "&code=" . urlencode($code);

    $subject = "Verify subscription to Task Planner";
    $message = '<p>Click the link below to verify your subscription to Task Planner:</p>
<p><a id="verification-link" href="' . $verification_link . '">Verify Subscription</a></p>';
    
    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    return mail($email, $subject, $message, $headers);
}

/**
 * Verifies an email subscription
 * 
 * @param string $email The email address to verify.
 * @param string $code The verification code.
 * @return bool True on success, false on failure.
 */
function verifySubscription(string $email, string $code): bool {
    $pendingFile = __DIR__ . '/pending_subscriptions.txt';
    $subscribersFile = __DIR__ . '/subscribers.txt';

    // Check if pending verification exists - FIX: Handle empty/null JSON
    if (!file_exists($pendingFile)) {
        return false;
    }
    
    $pendingContent = file_get_contents($pendingFile);
    if (empty($pendingContent)) {
        return false;
    }
    
    $pending = json_decode($pendingContent, true);
    if (!is_array($pending) || !isset($pending[$email]) || $pending[$email]['code'] !== $code) {
        return false;
    }

    // Add to subscribers - FIX: Handle empty/null JSON
    $subscribers = [];
    if (file_exists($subscribersFile)) {
        $subscribersContent = file_get_contents($subscribersFile);
        if (!empty($subscribersContent)) {
            $decodedSubscribers = json_decode($subscribersContent, true);
            $subscribers = is_array($decodedSubscribers) ? $decodedSubscribers : [];
        }
    }
    
    if (!in_array($email, $subscribers)) {
        $subscribers[] = $email;
        file_put_contents($subscribersFile, json_encode($subscribers));
    }

    // Remove from pending
    unset($pending[$email]);
    file_put_contents($pendingFile, json_encode($pending));

    return true;
}

/**
 * Unsubscribes an email from the subscribers list
 * 
 * @param string $email The email address to unsubscribe.
 * @return bool True on success, false on failure.
 */
function unsubscribeEmail(string $email): bool {
    $subscribers_file = __DIR__ . '/subscribers.txt';
    
    if (!file_exists($subscribers_file)) {
        return false;
    }
    
    $subscribers = json_decode(file_get_contents($subscribers_file), true);
    if (!$subscribers) {
        return false;
    }
    
    $key = array_search($email, $subscribers);
    
    if ($key !== false) {
        unset($subscribers[$key]);
        $subscribers = array_values($subscribers); // reindex array
        file_put_contents($subscribers_file, json_encode($subscribers));
        return true;
    }
    
    return false;
}

/**
 * Sends task reminders to all subscribers
 * Internally calls sendTaskEmail() for each subscriber
 */
function sendTaskReminders(): void {
    $subscribers_file = __DIR__ . '/subscribers.txt';
    
    if (!file_exists($subscribers_file)) {
        return;
    }
    
    $subscribers = json_decode(file_get_contents($subscribers_file), true);
    if (!$subscribers) {
        return;
    }
    
    $tasks = getAllTasks();
    
    // Get only pending (incomplete) tasks
    $pending_tasks = array_filter($tasks, function($task) {
        return !$task['completed'];
    });
    
    // If no pending tasks, don't send emails
    if (empty($pending_tasks)) {
        return;
    }
    
    // Send email to each subscriber
    foreach ($subscribers as $email) {
        sendTaskEmail($email, $pending_tasks);
    }
}

/**
 * Sends a task reminder email to a subscriber with pending tasks.
 *
 * @param string $email The email address of the subscriber.
 * @param array $pending_tasks Array of pending tasks to include in the email.
 * @return bool True if email was sent successfully, false otherwise.
 */
/**
 * Sends a task reminder email to a subscriber with pending tasks.
 *
 * @param string $email The email address of the subscriber.
 * @param array $pending_tasks Array of pending tasks to include in the email.
 * @return bool True if email was sent successfully, false otherwise.
 */
function sendTaskEmail(string $email, array $pending_tasks): bool {
    $subject = 'Task Planner - Pending Tasks Reminder';

    // Handle base URL for unsubscribe link
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = dirname($_SERVER['PHP_SELF'] ?? '');
    $base_url = "http://localhost:8000";

    $unsubscribe_link = $base_url . "/unsubscribe.php?email=" . urlencode($email);

    // Build the HTML message body
    $task_list = '';
    foreach ($pending_tasks as $task) {
        if (isset($task['name']) && !$task['completed']) {
            $task_list .= '<li>' . htmlspecialchars($task['name']) . '</li>';
        }
    }

    if (empty($task_list)) return false; // No tasks to send

    $message = '
        <html>
        <head><title>Task Reminder</title></head>
        <body>
            <h2>Pending Tasks Reminder</h2>
            <p>Here are your current pending tasks:</p>
            <ul>' . $task_list . '</ul>
            <p><a href="' . $unsubscribe_link . '">Unsubscribe from notifications</a></p>
        </body>
        </html>';

    // Set HTML email headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Task Planner <no-reply@example.com>\r\n";

    return mail($email, $subject, $message, $headers);
}
