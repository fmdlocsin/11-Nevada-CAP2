<?php
session_start();
include("database-connection.php");
include("check-login.php");
include("role_filter.php");

$data = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize the franchisee value from POST data
    $franchisee = isset($_POST['franchisee']) ? mysqli_real_escape_string($con, $_POST['franchisee']) : "";
    
    if(empty($franchisee)){
        echo json_encode(["status" => "error", "message" => "Franchise not specified"]);
        exit;
    }
    
    // Build the base query to fetch branches (ac_id and location) for the given franchisee and active agreements
    $query = "SELECT ac_id, location FROM agreement_contract WHERE franchisee = ? AND status = 'active'";
    $types = "s";
    $params = [$franchisee];
    
    // Get the role-based filter for area managers
    $filter = getAreaManagerFilter();
    if ($filter['clause'] != "") {
        // Append the extra filter clause; our helper returns a clause starting with AND.
        $query .= " " . $filter['clause'];
        $types .= "s";
        $params[] = $filter['param'];
    }
    
    // Order results by location
    $query .= " ORDER BY location";
    
    $stmt = $con->prepare($query);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => $con->error]);
        exit;
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && mysqli_num_rows($result) > 0) {
        $data['details'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $data['status'] = 'success';
    } else {
        $data['status'] = "error";
        $data['message'] = "No data found";
    }
    $stmt->close();
} else {
    $data['status'] = "error";
    $data['message'] = "Invalid request method";
}

echo json_encode($data);
?>
