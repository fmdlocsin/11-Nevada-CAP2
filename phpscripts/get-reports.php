<?php
session_start();
include("database-connection.php");
include("check-login.php");
$data = [];
$user_data = check_login($con);
$user_id = $user_data['user_id'];
$user_type = null;
$branch = null;

// Log user_id from session
error_log("Session User ID: " . print_r($user_id, true));

if ($user_id) {
    // Fetch user_type from users_accounts and branch from user_information
    $query = "
        SELECT ua.user_type, ui.branch 
        FROM users_accounts AS ua
        LEFT JOIN user_information AS ui ON ua.user_id = ui.user_id
        WHERE ua.user_id = ?
    ";

    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $user_type = trim(strtolower($row['user_type'])); // Normalize casing & spaces
        $branch = trim($row['branch']);
    }

    mysqli_stmt_close($stmt);
}

// **🔹 Debug Log Before the If Condition 🔹**
error_log(" User Type from DB: '" . $user_type . "'");
error_log(" User Branch from DB: '" . $branch . "'");
error_log(" Condition Check: " . ($user_type === 'branch_manager' ? 'TRUE' : 'FALSE') . " AND " . (!empty($branch) ? 'TRUE' : 'FALSE'));

if ($user_type === 'branch-manager' && !empty($branch)) {
    error_log(" Executing Branch Manager Query for branch: " . $branch);

    $query = "
        SELECT 
            ic.branch AS location,
            ic.franchisee AS franchisee,
            ic.inventory_id AS inventory_id,
            ic.datetime_added AS datetime_added
        FROM 
            item_inventory AS ic
        WHERE 
            ic.branch = ?
            AND ic.branch IS NOT NULL
            AND (ic.delivery IS NOT NULL OR ic.beginning IS NOT NULL OR ic.waste IS NOT NULL OR ic.sold IS NOT NULL)
        GROUP BY 
            ic.branch, ic.franchisee, ic.datetime_added
        ORDER BY 
            ic.datetime_added DESC
    ";

    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $branch);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
} else {
    error_log(" Executing Default Query (User Type: '" . $user_type . "' | Branch: '" . $branch . "')");

    // Default query for all other users
    $query = "
        SELECT
            ic.branch AS location,
            ic.franchisee AS franchisee,
            ic.inventory_id AS inventory_id,
            ic.datetime_added AS datetime_added
        FROM
            item_inventory AS ic
        WHERE
            ic.branch IS NOT NULL 
            AND (ic.delivery IS NOT NULL OR ic.beginning IS NOT NULL OR ic.waste IS NOT NULL OR ic.sold IS NOT NULL)
        GROUP BY
            ic.branch, ic.franchisee, ic.datetime_added
        ORDER BY
            ic.datetime_added DESC
    ";

    $result = mysqli_query($con, $query);
}

// Process the results
if ($result) {
    $reports = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $reports[] = [
            'franchisee' => $row['franchisee'],
            'location' => $row['location'],
            'datetime_added' => $row['datetime_added'],
            'inventory_id' => $row['inventory_id'],
        ];
    }

    $data['status'] = 'success';
    $data['user_id'] = $user_id;
    $data['user_type'] = $user_type;
    $data['branch'] = $branch;
    $data['reports'] = $reports;
} else {
    $data['status'] = 'error';
    $data['message'] = 'Failed to fetch reports.';
}

// Log final output
error_log("🔹 Final Response: " . json_encode($data));

header('Content-Type: application/json');
echo json_encode($data);
exit;
?>
