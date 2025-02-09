<?php
session_start();
include("database-connection.php");
include("check-login.php");

header('Content-Type: application/json');

$user_data = check_login($con);
$logged_in_user = $user_data['user_id'];

$data = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $input = file_get_contents("php://input");
    $transactions = json_decode($input, true);

    if (empty($transactions)) {
        echo json_encode(["status" => "error", "message" => "No transaction data received."]);
        exit();
    }

    foreach ($transactions as $transaction) {
        $franchise = mysqli_real_escape_string($con, $transaction['franchise']);
        
        $franchiseNameMap = [
            '11' => 'potato-corner',
            '12' => 'macao-imperial',
            '13' => 'auntie-anne'
        ];
        $franchise = $franchiseNameMap[$franchise] ?? strtolower(str_replace(' ', '-', $franchise));

        $location = isset($transaction['location']) ? mysqli_real_escape_string($con, $transaction['location']) : null;
        $productName = isset($transaction['productName']) ? mysqli_real_escape_string($con, $transaction['productName']) : null;
        $encoderName = mysqli_real_escape_string($con, $transaction['encoderName']);
        $date = mysqli_real_escape_string($con, $transaction['date']);
        $acId = mysqli_real_escape_string($con, $transaction['acId']);
        $grandTotal = isset($transaction['grandTotal']) ? mysqli_real_escape_string($con, $transaction['grandTotal']) : 0;

        // Ensure consistent service name formatting
        $servicesMap = [
            "dine-in" => "Dine-In",
            "take-out" => "Take-Out",
            "delivery" => "Delivery"
        ];
        $services = strtolower(mysqli_real_escape_string($con, $transaction['services']));
        $services = $servicesMap[$services] ?? "Unknown";

        // Payment details
        $cashCard = isset($transaction['cashCard']) ? mysqli_real_escape_string($con, $transaction['cashCard']) : 0;
        $gCash = isset($transaction['gCash']) ? mysqli_real_escape_string($con, $transaction['gCash']) : 0;
        $paymaya = isset($transaction['paymaya']) ? mysqli_real_escape_string($con, $transaction['paymaya']) : 0;
        $grabFood = isset($transaction['grabFood']) ? mysqli_real_escape_string($con, $transaction['grabFood']) : 0;
        $foodPanda = isset($transaction['foodPanda']) ? mysqli_real_escape_string($con, $transaction['foodPanda']) : 0;
        $totalSales = isset($transaction['totalSales']) ? mysqli_real_escape_string($con, $transaction['totalSales']) : 0;

        // Validate required fields
        if (empty($encoderName) || empty($acId) || empty($date) || empty($services)) {
            echo json_encode(["status" => "error", "message" => "Missing required fields."]);
            exit();
        }

        // Store transactions as a comma-separated string
        if ($services === "Dine-In" || $services === "Take-Out") {
            $transactionsString = implode(",", [$cashCard, $gCash, $paymaya, $totalSales]);
        } else {
            $transactionsString = implode(",", [$grabFood, $foodPanda, $totalSales]);
        }

        // Insert into the database
        $productNameValue = $productName ? "'$productName'" : "NULL";

        $insert_query = "INSERT INTO sales_report (
            ac_id,
            encoder_id,
            franchisee,
            services,
            transactions,
            grand_total,
            date_added,
            product_name
        ) VALUES (
            '$acId',
            '$logged_in_user',
            '$franchise',
            '$services',
            '$transactionsString',
            '$grandTotal',
            '$date',
            $productNameValue
        )";

        if (!mysqli_query($con, $insert_query)) {
            error_log("Database error: " . mysqli_error($con)); // Log error
            echo json_encode(["status" => "error", "message" => "Database error: " . mysqli_error($con)]);
            exit();
        }
    }

    echo json_encode(["status" => "success", "message" => "Sales report data saved successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
