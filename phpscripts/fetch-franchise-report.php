<?php
include("database-connection.php");

header('Content-Type: application/json'); // Ensure JSON response

// Get franchisee filter from GET request
$franchiseeFilter = isset($_GET['franchisee']) ? mysqli_real_escape_string($con, $_GET['franchisee']) : '';

// Base query
$query = "SELECT 
            c.franchisee AS franchisor, 
            c.location,  
            c.classification,  
            COUNT(CASE WHEN c.status = 'active' THEN 1 END) AS active_contracts,
            COUNT(CASE WHEN c.agreement_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH) THEN 1 END) AS expiring_contracts,
            COUNT(CASE WHEN c.agreement_date < CURDATE() THEN 1 END) AS expired_contracts,
            COUNT(CASE WHEN c.agreement_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) THEN 1 END) AS renewed_contracts,
            c.franchise_term AS start_date, 
            c.agreement_date AS expiration_date, 
            c.status AS remarks,
            TIMESTAMPDIFF(MONTH, c.franchise_term, c.agreement_date) AS contract_duration
          FROM agreement_contract c";

if (!empty($franchiseeFilter)) {
    $query .= " WHERE c.franchisee = '$franchiseeFilter'";
}

$query .= " GROUP BY c.franchisee, c.location, c.classification, c.franchise_term, c.agreement_date, c.status
            ORDER BY c.franchisee, c.location";

$result = mysqli_query($con, $query);


// Debugging: Check if query fails
if (!$result) {
    echo json_encode(["error" => mysqli_error($con)]); // Show SQL errors if any
    exit();
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Ensure values are properly set
    $row['location'] = !empty($row['location']) ? $row['location'] : "Unknown Location";  
    $row['classification'] = !empty($row['classification']) ? $row['classification'] : "Not Specified";  

    // Ensure values are numbers
    $renewed = (int) $row['renewed_contracts'];
    $expired = (int) $row['expired_contracts'];
    $total = $renewed + $expired;

    // Calculate renewal rate (avoid division by zero)
    $row['renewal_rate'] = ($total > 0) ? round(($renewed / $total) * 100, 2) . "%" : "0%";

    // Ensure `expiration_date` is valid
    if (empty($row['expiration_date']) || $row['expiration_date'] === "0000-00-00") {
        $row['expiration_date'] = "Invalid Date";
    }

    // Ensure `start_date` is valid
    if (empty($row['start_date']) || $row['start_date'] === "0000-00-00") {
        $row['start_date'] = "Invalid Date";
    }

    $data[] = $row;
}

echo json_encode($data, JSON_PRETTY_PRINT);
?>
