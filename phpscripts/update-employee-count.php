<?php
session_start();
include("database-connection.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    $min_employees = mysqli_real_escape_string($con, $_POST['min_employees']);

    $update_query = "UPDATE agreement_contract 
                     SET min_employees = '$min_employees'
                     WHERE ac_id = '$id'";

    if (mysqli_query($con, $update_query)) {
        echo json_encode(['status' => 'success', 'message' => 'Employee count updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($con)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
