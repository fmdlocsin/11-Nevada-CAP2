<?php
session_start();
include("database-connection.php"); // Ensure database connection is included

// Debugging: Log received data
error_log("Received Data: " . print_r($_POST, true));

// Ensure the database connection ($con) exists
if (!isset($con) || $con === null) {
    error_log("Database connection failed.");
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit();
}

// Check if required fields exist
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"], $_POST["reasonType"])) {
    $employeeId = $_POST["id"];
    $reasonType = $_POST["reasonType"];
    $reasonText = isset($_POST["reasonText"]) ? trim($_POST["reasonText"]) : "";

    // Debugging: Log received values
    error_log("Processing Employee ID: $employeeId, Reason Type: $reasonType, Reason: $reasonText");

    // Determine status and set certification_date for scheduled deletion
    if ($reasonType === "On Leave") {
        $newStatus = "unassigned";
        $certificateFileName = $reasonText;
        $certificationDate = NULL; // No scheduled deletion
    } elseif ($reasonType === "Resign") {
        $newStatus = "resigned";
        $certificateFileName = "";
        $certificationDate = date("Y-m-d H:i:s", strtotime("+7 days")); // 7 days later
    } else {
        error_log("Invalid reason type.");
        echo json_encode(["status" => "error", "message" => "Invalid reason type."]);
        exit();
    }

    // Debugging: Log SQL execution
    error_log("Updating Employee: ID=$employeeId, Status=$newStatus, Remarks=$certificateFileName, Delete At=$certificationDate");

    // Update employee information with scheduled deletion for Resign
    $query = $con->prepare("
        UPDATE user_information 
        SET employee_status = ?, 
            certificate_file_name = ?, 
            assigned_at = 0, 
            franchisee = 0, 
            branch = '', 
            user_shift = '', 
            certification_date = ?
        WHERE user_id = ?
    ");

    if (!$query) {
        error_log("SQL Prepare Error: " . $con->error);
        echo json_encode(["status" => "error", "message" => "SQL Prepare Error: " . $con->error]);
        exit();
    }

    $query->bind_param("sssi", $newStatus, $certificateFileName, $certificationDate, $employeeId);

    if ($query->execute()) {
        echo json_encode(["status" => "success", "message" => "Employee status updated successfully."]);
    } else {
        error_log("SQL Execute Error: " . $query->error);
        echo json_encode(["status" => "error", "message" => "Failed to update employee status."]);
    }

    $query->close();
    $con->close();
} else {
    error_log("Invalid request: " . print_r($_POST, true));
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
