<?php
require 'database-connection.php';

// Log incoming POST data
file_put_contents("debug_log.txt", "Received POST Data: " . print_r($_POST, true) . "\n", FILE_APPEND);

if (!isset($_POST['franchiseId']) || empty($_POST['franchiseId'])) {
    echo json_encode(["error" => "Invalid franchise ID received"]);
    exit();
}

$franchiseId = trim(mysqli_real_escape_string($con, $_POST['franchiseId']));

// Log the processed franchise ID
file_put_contents("debug_log.txt", "Processed Franchise ID: " . $franchiseId . "\n", FILE_APPEND);

// Fetch locations from agreement_contract where franchisee matches
$query = "SELECT ac_id, location FROM agreement_contract WHERE franchisee = '$franchiseId'";
$result = mysqli_query($con, $query);

if (!$result) {
    echo json_encode(["error" => "Database query failed: " . mysqli_error($con)]);
    file_put_contents("debug_log.txt", "SQL Error: " . mysqli_error($con) . "\n", FILE_APPEND);
    exit();
}

$locations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $locations[] = ["id" => $row['ac_id'], "name" => $row['location']];
}

// Log query results
file_put_contents("debug_log.txt", "Query Results: " . print_r($locations, true) . "\n", FILE_APPEND);

if (empty($locations)) {
    echo json_encode(["error" => "No locations found for this franchise"]);
    exit();
}

echo json_encode($locations);
?>
