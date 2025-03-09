<?php
session_start();
include 'database-connection.php';
include 'role_filter.php';

if (!isset($_POST['franchiseId']) || empty($_POST['franchiseId'])) {
    echo json_encode(["error" => "Invalid franchise ID received"]);
    exit();
}

$franchiseId = trim(mysqli_real_escape_string($con, $_POST['franchiseId']));

// Get the area manager filter (if applicable)
$filter = getAreaManagerFilter();

// Build the base query
$query = "SELECT ac_id, location FROM agreement_contract WHERE franchisee = '$franchiseId'";

// Append area code filter if the user is an area manager
if (!empty($filter['clause'])) {
    $area_code = mysqli_real_escape_string($con, $filter['param']);
    $query .= " AND area_code = '$area_code'";
}

$result = mysqli_query($con, $query);
if (!$result) {
    echo json_encode(["error" => "Database query failed: " . mysqli_error($con)]);
    exit();
}

$locations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $locations[] = ["id" => $row['ac_id'], "name" => $row['location']];
}

if (empty($locations)) {
    echo json_encode(["error" => "No locations found for this franchise"]);
    exit();
}

echo json_encode($locations);
?>
