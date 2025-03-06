<?php
include ("database-connection.php");

if (isset($_GET['franchise']) && !empty($_GET['franchise']) && $_GET['franchise'] != 'all') {
    $franchise = mysqli_real_escape_string($con, strtolower($_GET['franchise']));
    
    // Query to get unique locations based on the selected franchise
    $sql = "SELECT DISTINCT ac.location 
            FROM sales_report sr
            LEFT JOIN agreement_contract ac ON sr.ac_id = ac.ac_id
            WHERE LOWER(sr.franchisee) = '$franchise'
            ORDER BY ac.location ASC";
    $result = mysqli_query($con, $sql);

    $locations = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $locations[] = $row['location'];
        }
    }
    
    echo json_encode($locations);
} else {
    // If franchise is 'all' or not set, return an empty array
    echo json_encode([]);
}
?>
