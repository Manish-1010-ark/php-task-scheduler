<?php
require_once 'functions.php';

// Read verified subscribers
$subscribersFile = 'subscribers.txt';
if (!file_exists($subscribersFile)) {
    exit("No subscribers file found.\n");
}

$subscribersData = file_get_contents($subscribersFile);
$subscribers = json_decode($subscribersData, true);
if (!is_array($subscribers)) {
    exit("Invalid subscribers format.\n");
}

// Read tasks
$tasksFile = 'tasks.txt';
if (!file_exists($tasksFile)) {
    exit("No tasks file found.\n");
}

$tasksData = file_get_contents($tasksFile);
$tasks = json_decode($tasksData, true);
if (!is_array($tasks)) {
    exit("Invalid tasks format.\n");
}

// Filter only incomplete tasks
$pendingTasks = array_filter($tasks, function ($task) {
    return !$task['completed'];
});

// No pending tasks? Then skip sending
if (empty($pendingTasks)) {
    exit("No pending tasks to send.\n");
}

// Send email to each verified subscriber
foreach ($subscribers as $email) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendTaskEmail($email, $pendingTasks);
    }
}
?>
