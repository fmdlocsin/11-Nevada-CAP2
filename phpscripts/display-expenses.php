<?php
session_start();
include("database-connection.php");

$data = [];

// Start with a base query
$query = "SELECT * FROM expenses WHERE 1";

// Filter by franchise if provided
if (isset($_GET['franchise']) && $_GET['franchise'] != "") {
    $franchise = mysqli_real_escape_string($con, $_GET['franchise']);
    $query .= " AND franchisee = '$franchise'";
}

// Filter by location if provided
if (isset($_GET['location']) && $_GET['location'] != "") {
    $location = mysqli_real_escape_string($con, $_GET['location']);
    // The column name in your DB is "location"
    $query .= " AND location = '$location'";
}

// Filter by type (using expense_type) if provided
if (isset($_GET['category']) && $_GET['category'] != "") {
    $category = mysqli_real_escape_string($con, $_GET['category']);
    $query .= " AND expense_type = '$category'";
}

// Filter by start date if provided
if (isset($_GET['startDate']) && $_GET['startDate'] != "") {
    $startDate = mysqli_real_escape_string($con, $_GET['startDate']);
    $query .= " AND date_added >= '$startDate'";
}

// Filter by end date if provided
if (isset($_GET['endDate']) && $_GET['endDate'] != "") {
    $endDate = mysqli_real_escape_string($con, $_GET['endDate']);
    $query .= " AND date_added <= '$endDate'";
}

$result = mysqli_query($con, $query);

if (!$result) {
    $data['status'] = "error";
    $data['message'] = mysqli_error($con);
} elseif (mysqli_num_rows($result) > 0) {
    $data['details'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data['details'][] = $row;
    }
    $data['status'] = 'success';
} else {
    $data['status'] = "error";
    $data['message'] = "No data found";
}

echo json_encode($data);
?>
