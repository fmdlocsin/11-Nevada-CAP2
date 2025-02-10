<?php
session_start();
include ("database-connection.php");

$data = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $formattedFranchisee = isset($_POST['formattedFranchisee']) ? mysqli_real_escape_string($con, $_POST['formattedFranchisee']) : "";
    $branchLocation = isset($_POST['branchLocation']) ? mysqli_real_escape_string($con, $_POST['branchLocation']) : "";
    $eatType = isset($_POST['eatType']) ? mysqli_real_escape_string($con, $_POST['eatType']) : ""; // Get eat type

    if (empty($formattedFranchisee)) {
        echo json_encode(["status" => "error", "message" => "Missing franchise"]);
        exit;
    }

    // Base SQL query
    $sql = "SELECT ac_id, franchisee, location, classification 
            FROM agreement_contract 
            WHERE franchisee = '$formattedFranchisee' 
            AND status = 'active'";

    // Exclude kiosks if "Dine In" is selected
    if ($eatType === "DineIn") {
        $sql .= " AND classification != 'Kiosk'";
    }

    // If filtering by specific location
    if (!empty($branchLocation)) {
        $sql .= " AND location = '$branchLocation'";
    }

    $result = mysqli_query($con, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $data['details'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $data['status'] = 'success';
    } else {
        $data['status'] = "error";
        $data['message'] = "No branches found for this franchise.";
    }

    echo json_encode($data);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
?>
