<?php
session_start();
include("database-connection.php");
include("check-login.php");
include("role_filter.php");

if (isset($_GET['franchise']) && !empty($_GET['franchise']) && $_GET['franchise'] != 'all') {
    $franchise = mysqli_real_escape_string($con, strtolower($_GET['franchise']));
    
    // Base query to fetch distinct locations based on the selected franchise.
    $query = "SELECT DISTINCT ac.location 
              FROM sales_report sr
              LEFT JOIN agreement_contract ac ON sr.ac_id = ac.ac_id
              WHERE LOWER(sr.franchisee) = ?";
    $types = "s";
    $params = [$franchise];
    
    // Get the role-based filter for area managers.
    $filter = getAreaManagerFilter();
    if ($filter['clause'] != "") {
        // Append the area_code condition from role_filter.
        // Our helper returns a clause like "AND area_code = ?"
        $query .= " " . $filter['clause'];
        $types .= "s";
        $params[] = $filter['param'];
    }
    
    $query .= " ORDER BY ac.location ASC";
    
    // Prepare and execute the query.
    $stmt = $con->prepare($query);
    if (!$stmt) {
        echo json_encode([]);
        exit;
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $locations = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $locations[] = $row['location'];
        }
    }
    $stmt->close();
    echo json_encode($locations);
} else {
    // If franchise is 'all' or not set, return an empty array.
    echo json_encode([]);
}
?>
