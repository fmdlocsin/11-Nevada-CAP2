<?php
session_start();
include("database-connection.php");

header('Content-Type: application/json');

$data = [];

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['type'])) {
    $type = $_GET['type']; // "fully_staffed"

    if ($type === "fully_staffed") {
        $sql = "
            SELECT 
                ui.franchisee AS franchisee,  -- ✅ Get franchisee from user_information
                ac.ac_id, 
                ac.location AS branch, 
                COUNT(ui.user_id) AS employee_count,
                GROUP_CONCAT(ua.user_name SEPARATOR ', ') AS employee_names,
                GROUP_CONCAT(ui.user_shift SEPARATOR ', ') AS employee_shifts
            FROM agreement_contract ac
            LEFT JOIN user_information ui ON ac.ac_id = ui.assigned_at
            LEFT JOIN users_accounts ua ON ui.user_id = ua.user_id
            WHERE ac.ac_id IN (
                SELECT assigned_at 
                FROM user_information 
                GROUP BY assigned_at 
                HAVING COUNT(user_id) >= 2
            )
            GROUP BY ui.franchisee, ac.ac_id, ac.location
            ORDER BY ui.franchisee, ac.location;
        ";
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid type"]);
        exit();
    }

    $result = mysqli_query($con, $sql);

    if (!$result) {
        echo json_encode(["status" => "error", "message" => "SQL Error", "error" => mysqli_error($con)]);
        exit();
    }

    if (mysqli_num_rows($result) > 0) {
        $data['branches'] = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data['branches'][] = [
                'franchisee' => $row['franchisee'],  // ✅ Franchisee retrieved correctly
                'branch_name' => $row['branch'],
                'employee_count' => $row['employee_count'],
                'employee_details' => $row['employee_names'] ?: 'No employees assigned',
                'employee_shifts' => $row['employee_shifts'] ?: 'No shift info available'
            ];
        }
        $data['status'] = 'success';
    } else {
        $data['status'] = 'error';
        $data['message'] = 'No fully staffed branches found';
    }
} else {
    $data['status'] = 'error';
    $data['message'] = 'Invalid request method';
}

// Return JSON response
echo json_encode($data);
?>
