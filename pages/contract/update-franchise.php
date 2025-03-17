<?php
session_start();
include("../../phpscripts/database-connection.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize inputs
    $id = mysqli_real_escape_string($con, $_POST['id']);
    $franchisee = mysqli_real_escape_string($con, $_POST['franchisee']);
    $location = mysqli_real_escape_string($con, $_POST['location']);
    $classification = mysqli_real_escape_string($con, $_POST['classification']);
    $min_employees = mysqli_real_escape_string($con, $_POST['min_employees']);
    $agreement_date = mysqli_real_escape_string($con, $_POST['agreement_date']);
    $franchise_fee = mysqli_real_escape_string($con, $_POST['franchise_fee']);
    $franchise_package = mysqli_real_escape_string($con, $_POST['franchise_package']);
    $bond = mysqli_real_escape_string($con, $_POST['bond']);
    $extra_note = mysqli_real_escape_string($con, $_POST['extra_note']);

    // Perform the UPDATE query
    $query = "UPDATE agreement_contract 
              SET franchisee = '$franchisee', 
                  location = '$location', 
                  classification = '$classification',
                  min_employees = '$min_employees',
                  agreement_date = '$agreement_date', 
                  franchise_fee = '$franchise_fee', 
                  franchise_package = '$franchise_package', 
                  bond = '$bond', 
                  extra_note = '$extra_note' 
              WHERE ac_id = '$id'";

    if (mysqli_query($con, $query)) {
        // Redirect back to the franchise details page with a success message
        header("Location: franchisedetails.php?id=$id&status=success");
        exit;
    } else {
        // Show an error message if the update fails
        echo "Error updating record: " . mysqli_error($con);
    }
} else {
    // Show an error message if the request method is invalid
    echo "Invalid request method.";
}
?>
