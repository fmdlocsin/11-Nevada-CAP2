<?php
session_start();
header('Content-Type: application/json');

// Debug: Log session activity
file_put_contents("debug_session.txt", "AJAX Test - Session Data: " . print_r($_SESSION, true) . "\n", FILE_APPEND);

echo json_encode([
    "status" => isset($_SESSION['user_id']) ? "success" : "error",
    "message" => isset($_SESSION['user_id']) ? "Session is active in AJAX" : "Session expired in AJAX",
    "user_id" => $_SESSION['user_id'] ?? null
]);
?>
