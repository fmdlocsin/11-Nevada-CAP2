<?php
session_start();
include("database-connection.php");
include("check-login.php");
include("role_filter.php");

$data = [];
$user_data = check_login($con);
$user_id = $user_data['user_id'];
$user_type = null;
$branch = null;

// Fetch user_type and branch from the database
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
    $user_type = trim(strtolower($row['user_type']));
    $branch = trim($row['branch']);
}
mysqli_stmt_close($stmt);

error_log("User Type: " . $user_type);
error_log("User Branch: " . $branch);

// If the user is a branch manager, use that query.
if ($user_type === 'branch_manager' && !empty($branch)) {
    error_log("Executing Branch Manager Query for branch: " . $branch);
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
} 
// Else if the user is an area manager, filter by area_code using the agreement_contract table.
else if ($user_type === 'area-manager') {
    $filter = getAreaManagerFilter();
    error_log("Executing Area Manager Query with area_code: " . $filter['param']);
    
    // Join on the branch field from item_inventory matching the location in agreement_contract.
    $query = "
        SELECT 
            ic.branch AS location,
            ic.franchisee AS franchisee,
            ic.inventory_id AS inventory_id,
            ic.datetime_added AS datetime_added,
            ac.area_code
        FROM 
            item_inventory AS ic
        LEFT JOIN agreement_contract ac ON ic.branch = ac.location
        WHERE 
            ac.area_code = ?
            AND ic.branch IS NOT NULL
            AND (ic.delivery IS NOT NULL OR ic.beginning IS NOT NULL OR ic.waste IS NOT NULL OR ic.sold IS NOT NULL)
        GROUP BY 
            ic.branch, ic.franchisee, ic.datetime_added
        ORDER BY 
            ic.datetime_added DESC
    ";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $filter['param']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
} 
// Otherwise, default query for all other users.
else {
    error_log("Executing Default Query (User Type: " . $user_type . " | Branch: " . $branch . ")");
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
            'franchisee'    => $row['franchisee'],
            'location'      => $row['location'],
            'datetime_added'=> $row['datetime_added'],
            'inventory_id'  => $row['inventory_id']
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

error_log("Final Response: " . json_encode($data));

header('Content-Type: application/json');
echo json_encode($data);
exit;
?>
