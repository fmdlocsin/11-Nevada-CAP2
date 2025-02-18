<?php
session_start();
header('Content-Type: text/plain');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = rand(1000, 9999); // Simulating user login
    echo "Session started, user_id set to: " . $_SESSION['user_id'];
} else {
    echo "Session active, user_id: " . $_SESSION['user_id'];
}
?>
