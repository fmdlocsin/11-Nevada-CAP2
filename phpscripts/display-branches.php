<?php
session_start();
include ("database-connection.php");

$data = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $formattedFranchisee = isset($_POST['formattedFranchisee']) ? mysqli_real_escape_string($con, $_POST['formattedFranchisee']) : "";
    $branchLocation = isset($_POST['branchLocation']) ? mysqli_real_escape_string($con, $_POST['branchLocation']) : "";

    if (empty($formattedFranchisee)) {
        echo json_encode(["status" => "error", "message" => "Missing franchise"]);
        exit;
    }

    // If `branchLocation` is provided, filter by both franchise and location
    if (!empty($branchLocation)) {
        $sql = "SELECT ac_id, franchisee, location 
                FROM agreement_contract 
                WHERE franchisee = '$formattedFranchisee' 
                AND location = '$branchLocation' 
                AND status = 'active'";
    } else {
        // If `branchLocation` is not provided, return all active branches for the franchise
        $sql = "SELECT ac_id, franchisee, location 
                FROM agreement_contract 
                WHERE franchisee = '$formattedFranchisee' 
                AND status = 'active'";
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
