<?php

// ✅ Check if session is already started before calling session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_login($con)
{
    if (isset($_SESSION['user_email'])) {
        $user_email = $_SESSION['user_email'];

        $query = "SELECT * FROM users_accounts WHERE user_email = ? AND user_status = 'active' LIMIT 1";
        $stmt = $con->prepare($query);
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user_data = $result->fetch_assoc();

            // ✅ Store both email and username in the session
            $_SESSION['username'] = $user_data['user_name']; // Make sure 'user_name' is the correct column

            return $user_data;
        }
    }

    // Redirect to login if session is not valid
    header("Location: ./login.php");
    exit;
}
