<?php
session_start();
include ("database-connection.php");

$data = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Validate required fields
    if (
        empty($_POST['selectedFranchise']) || 
        empty($_POST['franchiseLocation']) || 
        empty($_POST['encoderName']) || 
        empty($_POST['dateToday']) || 
        empty($_POST['selectedExpense'])
    ) {
        $data['status'] = 'error';
        $data['message'] = 'All fields are required!';
    } else {
        // Sanitize and escape input data
        $selectedFranchise = mysqli_real_escape_string($con, $_POST['selectedFranchise']);
        $franchiseLocation = mysqli_real_escape_string($con, $_POST['franchiseLocation']);
        $encoderName = mysqli_real_escape_string($con, $_POST['encoderName']); // Should be encoder ID, fix below
        $dateToday = mysqli_real_escape_string($con, $_POST['dateToday']);
        $selectedExpense = mysqli_real_escape_string($con, $_POST['selectedExpense']);
        $expenseType = isset($_POST['expenseType']) ? mysqli_real_escape_string($con, $_POST['expenseType']) : '';
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
        $description = isset($_POST['description']) ? mysqli_real_escape_string($con, $_POST['description']) : '';
        $otherDetails = isset($_POST['otherDetails']) ? mysqli_real_escape_string($con, $_POST['otherDetails']) : '';

        // Validate amount
        if ($amount <= 0) {
            $data['status'] = 'error';
            $data['message'] = 'Amount must be greater than 0!';
            echo json_encode($data);
            exit();
        }

        // Ensure Encoder ID is retrieved correctly (Assuming it's stored in `user_data`)
        $encoderId = $_SESSION['user_id']; // Assuming session stores user_id

        // Fix column name: expense_catergory → expense_category
        $query = "INSERT INTO expenses 
                  (encoder_id, franchisee, location, expense_category, expense_type, expense_purpose, expense_amount, expense_description, date_added) 
                  VALUES 
                  ('$encoderId', '$selectedFranchise', '$franchiseLocation', '$selectedExpense', '$expenseType', '$otherDetails', '$amount', '$description', '$dateToday')";

        if (mysqli_query($con, $query)) {
            $data['status'] = 'success';
            $data['message'] = 'Expense details saved successfully!';
        } else {
            $data['status'] = 'error';
            $data['message'] = 'Failed to save. Error: ' . mysqli_error($con);
        }
    }

    echo json_encode($data);
}
?>
