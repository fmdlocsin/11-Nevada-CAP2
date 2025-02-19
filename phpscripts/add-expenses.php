<?php
session_start();
include ("database-connection.php");

// Enable debugging
// ini_set('display_errors', 0);
// ini_set('log_errors', 1);
// ini_set('error_log', 'error_log.txt'); // Log errors to a file
// error_reporting(E_ALL);

$data = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Debug: Log received data
    // error_log("Received POST Data: " . print_r($_POST, true));

    // Validate required fields
    if (
        empty($_POST['selectedFranchise']) || 
        empty($_POST['franchiseLocation']) || 
        empty($_POST['encoderId']) ||  
        empty($_POST['dateToday']) || 
        empty($_POST['selectedExpense'])
    ) {
        $data['status'] = 'error';
        $data['message'] = 'All fields are required!';
        echo json_encode($data);
        exit();
    }

    // Sanitize input data
    $selectedFranchise = mysqli_real_escape_string($con, $_POST['selectedFranchise']);
    $franchiseLocation = mysqli_real_escape_string($con, $_POST['franchiseLocation']);
    $encoderId = mysqli_real_escape_string($con, $_POST['encoderId']);
    $dateToday = mysqli_real_escape_string($con, $_POST['dateToday']);
    $selectedExpense = mysqli_real_escape_string($con, $_POST['selectedExpense']);
    $expenseType = isset($_POST['expenseType']) ? mysqli_real_escape_string($con, $_POST['expenseType']) : '';
    $amount = isset($_POST['amount']) ? mysqli_real_escape_string($con, $_POST['amount']) : '';
    $description = isset($_POST['description']) ? mysqli_real_escape_string($con, $_POST['description']) : '';
    $otherDetails = isset($_POST['otherDetails']) ? mysqli_real_escape_string($con, $_POST['otherDetails']) : '';

    // Fix database column name typo (if it exists)
    $query = "INSERT INTO expenses 
              (encoder_id, franchisee, location, expense_catergory, expense_type, expense_purpose, expense_amount, expense_description, date_added) 
              VALUES 
              ('$encoderId', '$selectedFranchise', '$franchiseLocation', '$selectedExpense', '$expenseType', '$otherDetails', '$amount', '$description', '$dateToday')";

    if (!mysqli_query($con, $query)) {
        $data['status'] = 'error';
        $data['message'] = 'Database error: ' . mysqli_error($con);
    } else {
        $data['status'] = 'success';
        $data['message'] = 'Expense details saved successfully!';
    }
}

// Ensure the response is JSON
header('Content-Type: application/json');
echo json_encode($data);
exit();
?>
