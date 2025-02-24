<?php
include("database-connection.php");

header('Content-Type: application/json'); // Ensure JSON response

$query = "SELECT 
            c.franchisee AS franchisor, 
            c.space_number AS branch_name,
            SUM(CASE WHEN c.status = 'active' THEN 1 ELSE 0 END) AS active_leases,
            SUM(CASE WHEN c.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH) THEN 1 ELSE 0 END) AS expiring_leases,
            SUM(CASE WHEN c.end_date < CURDATE() THEN 1 ELSE 0 END) AS expired_leases,
            c.start_date, 
            c.end_date AS expiration_date, 
            c.lessor_address1 AS location
          FROM lease_contract c
          GROUP BY c.franchisee, c.space_number, c.start_date, c.end_date, c.lessor_address1
          ORDER BY c.franchisee, c.space_number";

$result = mysqli_query($con, $query);

if (!$result) {
    echo json_encode(["error" => "Database query failed: " . mysqli_error($con)], JSON_PRETTY_PRINT);
    exit();
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Ensure date formatting consistency
    $row['start_date'] = date("Y-m-d", strtotime($row['start_date']));
    $row['expiration_date'] = date("Y-m-d", strtotime($row['expiration_date']));
    $data[] = $row;
}

// Send JSON response
echo json_encode($data, JSON_PRETTY_PRINT);
?>
