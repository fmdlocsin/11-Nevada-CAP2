<?php
include("database-connection.php");

header('Content-Type: application/json'); // Ensure JSON response

$query = "SELECT c.franchisee AS franchisor, c.branch_name, 
          COUNT(CASE WHEN c.status = 'active' THEN 1 END) AS active_leases,
          COUNT(CASE WHEN c.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH) THEN 1 END) AS expiring_leases,
          COUNT(CASE WHEN c.end_date < CURDATE() THEN 1 END) AS expired_leases,
          c.start_date, c.end_date AS expiration_date, c.location
          FROM lease_contract c
          GROUP BY c.franchisee, c.branch_name
          ORDER BY c.franchisee, c.branch_name";

$result = mysqli_query($con, $query);

// Debugging: Check if query fails
if (!$result) {
    echo json_encode(["error" => mysqli_error($con)]); // Show SQL errors if any
    exit();
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data, JSON_PRETTY_PRINT);
?>
