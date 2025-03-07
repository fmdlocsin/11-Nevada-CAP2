<?php
session_start();
include("database-connection.php");

$data = []; // Prepare response data

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $user_email = trim($_POST['userEmail']); // Sanitize inputs
    $user_password = trim($_POST['userPassword']);

    // Input validation
    if (empty($user_email) || empty($user_password)) {
        $data = [
            'status' => "error",
            'message' => "Please enter your email and password."
        ];
    } else {
        // Query database
        $get_users_query = "SELECT * FROM users_accounts WHERE user_email = ? AND user_status = 'active'";
        $stmt = $con->prepare($get_users_query);
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $result = $stmt->get_result();
        $fetch_users = $result->fetch_assoc();

        if ($result && $result->num_rows > 0) {
            // Compare password using base64_encode as before
            if (base64_encode($user_password) === $fetch_users['user_password']) {
                // Store common user details in the session
                $_SESSION['user_email'] = $fetch_users['user_email'];
                $_SESSION['user_type'] = $fetch_users['user_type'];
                $_SESSION['user_name'] = $fetch_users['user_name'];
                
                if ($fetch_users['user_type'] == 'area-manager') {
                    // Since area_code is stored in agreement_contract, assign a default or lookup value here.
                    $_SESSION['area_code'] = '1014'; // Default value for demonstration
                }                

                $data = [
                    'status' => "success",
                    'message' => "Login successful.",
                    'user_type' => $fetch_users['user_type']
                ];
            } else {
                $data = [
                    'status' => "error",
                    'message' => "Incorrect password."
                ];
            }
        } else {
            $data = [
                'status' => "error",
                'message' => "No user found with this email."
            ];
        }

        $stmt->close();
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($data);
exit; // Stop script execution after sending response
?>
