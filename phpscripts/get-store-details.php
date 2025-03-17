<?php
session_start();
include ("database-connection.php");

$data = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($con, $_POST['id']);

    // Fetch the minimum number of employees for this branch
    $sqlBranch = "SELECT min_employees FROM agreement_contract WHERE ac_id = '$id'";
    $resultBranch = mysqli_query($con, $sqlBranch);
    $minEmployees = 0;
    if ($resultBranch && mysqli_num_rows($resultBranch) > 0) {
        $rowBranch = mysqli_fetch_assoc($resultBranch);
        $minEmployees = $rowBranch['min_employees'];
    }
    
    // Fetch the employees assigned to this branch
    $sql = "
        SELECT 
            ua.user_id, 
            ua.user_name,
            ui.user_shift
        FROM 
            user_information ui
        LEFT JOIN 
            users_accounts ua ON ui.user_id = ua.user_id
        WHERE 
            ui.assigned_at = '$id'
    ";

    $result = mysqli_query($con, $sql);

    if ($result) {
        $data['employees'] = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // Optionally include the branch's min_employees in each row if needed
            $row['min_employees'] = $minEmployees;
            $data['employees'][] = $row;
        }
        $data['status'] = 'success';
        // Also return min_employees separately in case there are no employees
        $data['min_employees'] = $minEmployees;
    } else {
        $data['status'] = 'error';
        $data['message'] = 'No data found';
    }
}

echo json_encode($data);
?>
