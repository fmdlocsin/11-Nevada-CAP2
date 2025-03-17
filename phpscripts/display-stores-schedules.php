<?php
session_start();
ob_start(); // Start output buffering for debugging

include("database-connection.php");

$data = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $str = mysqli_real_escape_string($con, $_POST['str']);

    // Map franchise string to database format
    if ($str === "potatoCorner") {
        $franchisee = "potato-corner";
    } else if ($str === "auntieAnne") {
        $franchisee = "auntie-anne";
    } else if ($str === "macaoImperial") {
        $franchisee = "macao-imperial";
    } else {
        $data['status'] = 'error';
        $data['message'] = 'Invalid franchisee specified';
        echo json_encode($data);
        exit();
    }

    // Updated query:
    // We select min_employees from agreement_contract and then filter
    // only those branches where the number of assigned employees is greater than or equal to min_employees.
    $sql = "
        SELECT 
            ac.ac_id, 
            ac.location AS branch, 
            ac.franchisee, 
            ac.min_employees,
            COUNT(ui.user_id) AS employee_count,
            GROUP_CONCAT(ua.user_name SEPARATOR ', ') AS employee_details
        FROM agreement_contract ac
        LEFT JOIN user_information ui 
            ON ac.ac_id = ui.assigned_at
        LEFT JOIN users_accounts ua
            ON ui.user_id = ua.user_id
        WHERE ac.franchisee = '$franchisee'
        GROUP BY ac.ac_id, ac.location, ac.franchisee, ac.min_employees
        HAVING employee_count >= ac.min_employees;
    ";

    // Log SQL query for debugging
    error_log("SQL Query: " . $sql);

    $result = mysqli_query($con, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $data['employees'] = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data['employees'][] = [
                'assigned_at' => $row['ac_id'], // Matches JS `data-ac-id`
                'branch' => $row['branch'],
                'franchisee' => $row['franchisee'],
                'employee_count' => $row['employee_count'],
                'min_employees' => $row['min_employees'],
                'employee_details' => $row['employee_details']
            ];
        }
        $data['status'] = 'success';
    } else {
        $data['status'] = 'error';
        $data['message'] = 'No branches found with full manpower (minimum requirement met).';
    }
} else {
    $data['status'] = 'error';
    $data['message'] = 'Invalid request method';
}

// Capture debug output
$debug_output = ob_get_clean(); // Capture all output
$data['debug_output'] = $debug_output; // Add to response for debugging

// Return JSON response
header('Content-Type: application/json');
echo json_encode($data);

// Log the final response for debugging
error_log("Response Data: " . json_encode($data));
?>
