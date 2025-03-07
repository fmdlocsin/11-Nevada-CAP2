<?php
session_start();
include("database-connection.php");
include("check-login.php");
include("role_filter.php");

$data = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $formattedFranchisee = isset($_POST['formattedFranchisee']) ? mysqli_real_escape_string($con, $_POST['formattedFranchisee']) : "";
    $branchLocation = isset($_POST['branchLocation']) ? mysqli_real_escape_string($con, $_POST['branchLocation']) : "";
    $eatType = isset($_POST['eatType']) ? mysqli_real_escape_string($con, $_POST['eatType']) : "";

    if (empty($formattedFranchisee)) {
        echo json_encode(["status" => "error", "message" => "Missing franchise"]);
        exit;
    }

    // Build the base query using prepared statement syntax.
    $query = "SELECT ac_id, franchisee, location, classification FROM agreement_contract WHERE franchisee = ? AND status = 'active'";
    $types = "s";
    $params = [$formattedFranchisee];

    // Exclude kiosks if "DineIn" is selected.
    if ($eatType === "DineIn") {
        $query .= " AND classification != 'Kiosk'";
    }

    // Filter by specific location if provided.
    if (!empty($branchLocation)) {
        $query .= " AND location = ?";
        $types .= "s";
        $params[] = $branchLocation;
    }

    // Get the role-based filter for area managers.
    $filter = getAreaManagerFilter();
    if ($filter['clause'] !== "") {
        $query .= " " . $filter['clause'];
        $types .= "s";
        $params[] = $filter['param'];
    }

    $query .= " ORDER BY location";

    $stmt = $con->prepare($query);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => $con->error]);
        exit;
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $data['details'] = $result->fetch_all(MYSQLI_ASSOC);
        $data['status'] = 'success';
    } else {
        $data['status'] = "error";
        $data['message'] = "No branches found for this franchise.";
    }

    $stmt->close();
    echo json_encode($data);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
?>
