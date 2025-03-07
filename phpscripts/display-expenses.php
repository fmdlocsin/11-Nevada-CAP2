<?php
session_start();
include("database-connection.php");
include("check-login.php");
include("role_filter.php");

$data = [];

// Build the base query joining expenses (alias e) with agreement_contract (alias ac)
$query = "SELECT e.*, ac.area_code 
          FROM expenses e 
          LEFT JOIN agreement_contract ac ON e.location = ac.ac_id 
          WHERE 1";

// Filter by franchise if provided
if (isset($_GET['franchise']) && $_GET['franchise'] != "") {
    $franchise = mysqli_real_escape_string($con, $_GET['franchise']);
    $query .= " AND e.franchisee = '$franchise'";
}

// Filter by location if provided
// (Assuming your dropdown returns the ac_id from agreement_contract)
if (isset($_GET['location']) && $_GET['location'] != "") {
    $location = mysqli_real_escape_string($con, $_GET['location']);
    $query .= " AND e.location = '$location'";
}

// Filter by expense type (using expense_type) if provided
if (isset($_GET['category']) && $_GET['category'] != "") {
    $category = mysqli_real_escape_string($con, $_GET['category']);
    $query .= " AND e.expense_type = '$category'";
}

// Filter by start date if provided
if (isset($_GET['startDate']) && $_GET['startDate'] != "") {
    $startDate = mysqli_real_escape_string($con, $_GET['startDate']);
    $query .= " AND e.date_added >= '$startDate'";
}

// Filter by end date if provided
if (isset($_GET['endDate']) && $_GET['endDate'] != "") {
    $endDate = mysqli_real_escape_string($con, $_GET['endDate']);
    $query .= " AND e.date_added <= '$endDate'";
}

// Apply area manager filter using role_filter.php
$filter = getAreaManagerFilter();
if ($filter['clause'] != "") {
    $area = mysqli_real_escape_string($con, $filter['param']);
    $query .= " AND ac.area_code = '$area'";
}

// Order the results
$query .= " ORDER BY e.date_added DESC";

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
