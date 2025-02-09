<?php
session_start();
include("database-connection.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dashFranchise = isset($_POST['dashFranchise']) ? mysqli_real_escape_string($con, $_POST['dashFranchise']) : "";
    $dashServices = isset($_POST['dashServices']) ? mysqli_real_escape_string($con, $_POST['dashServices']) : "";
    $branchLocation = isset($_POST['branchLocation']) ? mysqli_real_escape_string($con, $_POST['branchLocation']) : "";

    // Ensure at least one parameter is provided
    if (empty($dashFranchise) || empty($branchLocation)) {
        echo json_encode(["status" => "error", "message" => "Missing franchise or location"]);
        exit;
    }

    // Modify SQL query to include `location`
    $sql = "SELECT sr.*, ac.location 
            FROM sales_report sr
            LEFT JOIN agreement_contract ac ON sr.ac_id = ac.ac_id
            WHERE sr.franchisee = '$dashFranchise' 
            AND ac.location = '$branchLocation'";

    // If a specific service type is chosen, filter by it
    if ($dashServices !== "all") {
        $sql .= " AND sr.services = '$dashServices'";
    }

    $result = mysqli_query($con, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $data['sales_info'] = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data['sales_info'][] = $row;
        }
        $data['status'] = 'success';
    } else {
        $data['status'] = "error";
        $data['message'] = "No data found for this location and franchise.";
    }
    
    echo json_encode($data);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
?>
