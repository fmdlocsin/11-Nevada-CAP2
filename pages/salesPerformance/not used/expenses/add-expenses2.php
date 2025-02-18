<?php
session_start();
include ("database-connection.php");

// Ensure JSON response
header('Content-Type: application/json');

// Enable debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$data = [];

// ✅ Debug session before checking user_id
file_put_contents("debug_session.txt", "add-expenses.php - Session Data: " . print_r($_SESSION, true) . "\n", FILE_APPEND);

// ✅ Ensure session is active
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $data['status'] = 'error';
    $data['message'] = 'Session expired. Please log in again.';
    echo json_encode($data);
    exit();
}

$encoderId = $_SESSION['user_id']; // Retrieve user_id from session

// ✅ Ensure request is POST
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    $data['status'] = 'error';
    $data['message'] = 'Invalid request method!';
    echo json_encode($data);
    exit();
}

// ✅ Validate required fields
$requiredFields = ['selectedFranchise', 'franchiseLocation', 'encoderName', 'dateToday', 'selectedExpense'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        $data['status'] = 'error';
        $data['message'] = "Missing required field: $field";
        echo json_encode($data);
        exit();
    }
}

// ✅ Sanitize input data
$selectedFranchise = mysqli_real_escape_string($con, $_POST['selectedFranchise']);
$franchiseLocation = mysqli_real_escape_string($con, $_POST['franchiseLocation']);
$encoderName = mysqli_real_escape_string($con, $_POST['encoderName']);
$dateToday = mysqli_real_escape_string($con, $_POST['dateToday']);
$selectedExpense = mysqli_real_escape_string($con, $_POST['selectedExpense']);
$expenseType = isset($_POST['expenseType']) ? mysqli_real_escape_string($con, $_POST['expenseType']) : '';
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$description = isset($_POST['description']) ? mysqli_real_escape_string($con, $_POST['description']) : '';
$otherDetails = isset($_POST['otherDetails']) ? mysqli_real_escape_string($con, $_POST['otherDetails']) : '';

// ✅ Validate amount
if ($amount <= 0) {
    $data['status'] = 'error';
    $data['message'] = 'Amount must be greater than 0!';
    echo json_encode($data);
    exit();
}

// ✅ Insert into database
$query = "INSERT INTO expenses 
          (encoder_id, franchisee, location, expense_category, expense_type, expense_purpose, expense_amount, expense_description, date_added) 
          VALUES 
          ('$encoderId', '$selectedFranchise', '$franchiseLocation', '$selectedExpense', '$expenseType', '$otherDetails', '$amount', '$description', '$dateToday')";

if (mysqli_query($con, $query)) {
    $data['status'] = 'success';
    $data['message'] = 'Expense details saved successfully!';
} else {
    // ✅ Log database error
    file_put_contents("debug_log.txt", "Database Error: " . mysqli_error($con) . "\n", FILE_APPEND);
    $data['status'] = 'error';
    $data['message'] = 'Database error occurred. Please try again later.';
}

// ✅ Send JSON response
echo json_encode($data);
exit();
?>
