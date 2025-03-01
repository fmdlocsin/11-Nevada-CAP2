<?php
include("database-connection.php");

// Delete employees scheduled for removal
$query = "DELETE FROM user_information WHERE delete_at IS NOT NULL AND delete_at <= NOW()";
$result = $con->query($query);

if ($result) {
    error_log("Expired employees deleted successfully.");
} else {
    error_log("Error deleting expired employees: " . $con->error);
}

$con->close();
?>
