<?php
include("database-connection.php");

header('Content-Type: application/json'); // Ensure JSON response

$query = "SELECT 
            c.franchisee AS franchisor, 
            c.location AS branch, 
            COUNT(CASE WHEN c.status = 'active' THEN 1 END) AS active_contracts,
            COUNT(CASE WHEN c.agreement_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH) THEN 1 END) AS expiring_contracts,
            COUNT(CASE WHEN c.agreement_date < CURDATE() THEN 1 END) AS expired_contracts,
            COUNT(CASE WHEN c.agreement_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) THEN 1 END) AS renewed_contracts,
            c.franchise_term AS start_date, 
            c.agreement_date AS expiration_date, -- Directly using agreement_date
            c.location, 
            c.status AS remarks
          FROM agreement_contract c
          GROUP BY c.franchisee, c.location, c.franchise_term, c.agreement_date, c.status
          ORDER BY c.franchisee, c.location";

$result = mysqli_query($con, $query);

// Debugging: Check if query fails
if (!$result) {
    echo json_encode(["error" => mysqli_error($con)]); // Show SQL errors if any
    exit();
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Ensure values are numbers
    $renewed = (int) $row['renewed_contracts'];
    $expired = (int) $row['expired_contracts'];
    $total = $renewed + $expired;

    // Calculate renewal rate (avoid division by zero)
    $row['renewal_rate'] = ($total > 0) ? round(($renewed / $total) * 100, 2) . "%" : "0%";

    // Debugging: Check if expiration_date is correctly fetched
    if (!isset($row['expiration_date'])) {
        $row['expiration_date'] = "N/A"; // If still undefined, mark as "N/A"
    }

    $data[] = $row;
}

echo json_encode($data, JSON_PRETTY_PRINT);
?>
