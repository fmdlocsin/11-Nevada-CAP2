<?php
session_start();
include("database-connection.php");

header('Content-Type: application/json');

$data = [];

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['type'])) {
    $type = $_GET['type']; // "fully_staffed" or "understaffed"
    // Get franchise filter (if provided)
    $franchiseeFilter = isset($_GET['franchisee']) ? mysqli_real_escape_string($con, $_GET['franchisee']) : '';

    if ($type === "fully_staffed") {
        $sql = "SELECT 
                    ac.franchisee AS franchisee,
                    ac.ac_id, 
                    ac.location AS branch, 
                    ac.min_employees,
                    COUNT(ui.user_id) AS employee_count,
                    GROUP_CONCAT(ua.user_name SEPARATOR ', ') AS employee_names,
                    GROUP_CONCAT(ui.user_shift SEPARATOR ', ') AS employee_shifts,
                    GROUP_CONCAT(ua.user_phone_number SEPARATOR ', ') AS phone_numbers,  
                    GROUP_CONCAT(ua.user_address SEPARATOR ', ') AS addresses
                FROM agreement_contract ac
                LEFT JOIN user_information ui ON ac.ac_id = ui.assigned_at
                LEFT JOIN users_accounts ua ON ui.user_id = ua.user_id";
        if (!empty($franchiseeFilter)) {
            // Apply filter on agreement_contract.franchisee
            $sql .= " WHERE ac.franchisee = '$franchiseeFilter'";
        }
        $sql .= " GROUP BY ac.franchisee, ac.ac_id, ac.location, ac.min_employees
                  HAVING employee_count >= ac.min_employees
                  ORDER BY ac.franchisee, ac.location;";
    } elseif ($type === "understaffed") {
        $sql = "SELECT 
                    ac.franchisee AS franchisee,
                    ac.ac_id, 
                    ac.location AS branch, 
                    ac.min_employees,
                    COUNT(ui.user_id) AS employee_count,
                    GROUP_CONCAT(ua.user_name SEPARATOR ', ') AS employee_names,
                    GROUP_CONCAT(ui.user_shift SEPARATOR ', ') AS employee_shifts,
                    GROUP_CONCAT(ua.user_phone_number SEPARATOR ', ') AS phone_numbers,  
                    GROUP_CONCAT(ua.user_address SEPARATOR ', ') AS addresses
                FROM agreement_contract ac
                LEFT JOIN user_information ui ON ac.ac_id = ui.assigned_at
                LEFT JOIN users_accounts ua ON ui.user_id = ua.user_id";
        if (!empty($franchiseeFilter)) {
            $sql .= " WHERE ac.franchisee = '$franchiseeFilter'";
        }
        $sql .= " GROUP BY ac.franchisee, ac.ac_id, ac.location, ac.min_employees
                  HAVING employee_count < ac.min_employees
                  ORDER BY ac.franchisee, ac.location;";
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
                'franchisee'     => $row['franchisee'],
                'branch_name'    => $row['branch'],
                'min_employees'  => $row['min_employees'],
                'employee_count' => $row['employee_count'],
                'employee_details' => $row['employee_names'] ?: 'No employees assigned',
                'employee_shifts'  => $row['employee_shifts'] ?: 'No shift info available',
                'phone_numbers'    => $row['phone_numbers'] ?: 'No phone info available',
                'addresses'        => $row['addresses'] ?: 'No address info available'
            ];
        }
        $data['status'] = 'success';
    } else {
        $data['status'] = 'error';
        $data['message'] = $type === "fully_staffed" ? 'No fully staffed branches found' : 'No understaffed branches found';
    }
} else {
    $data['status'] = 'error';
    $data['message'] = 'Invalid request method';
}

echo json_encode($data);
?>
