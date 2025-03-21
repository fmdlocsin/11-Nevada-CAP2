<?php
session_start();

include ("../../phpscripts/database-connection.php");
include ("../../phpscripts/check-login.php");

$id = isset($_GET['id']) ? $_GET['id'] : null;
$data = [];

// Updated query
$query = "
    SELECT DISTINCT sr.*, 
           ua.user_name AS encoder_name, 
           ac.franchisee AS franchisee_name, 
           ac.location AS franchise_location 
    FROM sales_report sr
    LEFT JOIN users_accounts ua ON sr.encoder_id = ua.user_id
    LEFT JOIN agreement_contract ac ON sr.ac_id = ac.ac_id
    WHERE sr.report_id = '$id'
    LIMIT 1
";


$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);

$servicesRaw = strtolower(trim($data['services']));
$serviceMap = [
    'dine-in' => 'Dine-In',
    'take-out' => 'Take-Out',
    'delivery' => 'Delivery'
];

$servicesFormatted = isset($serviceMap[$servicesRaw]) ? $serviceMap[$servicesRaw] : 'Delivery';

// Map database franchise names to properly formatted names
$franchiseNameMap = [
    "potato-corner" => "Potato Corner",
    "macao-imperial" => "Macao Imperial",
    "auntie-anne" => "Auntie Anne"
];

// Get the formatted franchise name
$formattedFranchiseName = isset($franchiseNameMap[$data['franchisee_name']]) ? 
    $franchiseNameMap[$data['franchisee_name']] : ucwords(str_replace("-", " ", $data['franchisee_name']));


// Convert transactions from a string back to an array
$transactions = explode(",", $data['transactions']);

// Ensure transactions have correct indexes
if (count($transactions) < 4 && ($servicesFormatted === "Dine-In" || $servicesFormatted === "Take-Out")) {
    while (count($transactions) < 4) {
        $transactions[] = 0; // Fill missing values with 0
    }
} elseif (count($transactions) < 3 && $servicesFormatted === "Delivery") {
    while (count($transactions) < 3) {
        $transactions[] = 0;
    }
}

// Assign transactions based on service type
if ($servicesFormatted === "Dine-In" || $servicesFormatted === "Take-Out") {
    $data['transactions'] = [
        "Cash/Card" => $transactions[0],
        "GCash" => $transactions[1],
        "Paymaya" => $transactions[2],
        "Other Sales" => $transactions[3]
    ];
} else {
    $data['transactions'] = [
        "GrabFood" => $transactions[0],
        "FoodPanda" => $transactions[1],
        "Other Sales" => $transactions[2]
    ];
}

}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../../assets/css/navbar.css">
    <link rel="stylesheet" href="../../assets/css/salesReport.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    
    <?php include '../../navbar.php'; ?>

    <section class="home">
        <header class="contractheader">
            <div class="container-header">
                <h1 class="title">View Sales Report</h1>
            </div>
        </header>
        <div class="container">
            <header class="header-report">Daily Sales Report</header>
            <!-- <header class="header-report">Daily Sales Report: <?php echo date("l, F d", strtotime($data['date_added'])); ?></header> -->
            <!-- Header section above the table -->
            <header class="header-info">
                <div class="header-section encoder">
                    <span class="header-label">Encoder:</span> <?php echo htmlspecialchars($data['encoder_name']); ?>
                </div>
                <div class="header-section date">
                <span class="header-label">Date:</span> <?php echo htmlspecialchars($data['date_added']); ?>
                </div>
            </header>
            <header class="header-info2">
            <div class="header-section2">
            <span class="header-label">Franchisee:</span> <?php echo htmlspecialchars($formattedFranchiseName); ?>

            </div>
            <div class="header-section location">
                <span class="header-label">Location:</span> <?php echo htmlspecialchars($data['franchise_location']); ?>
            </div>
            </header>
            
            <!-- Table for Sales Report -->
            <table>
                 <caption>Product Name: <strong><?php echo htmlspecialchars($data['product_name']); ?></strong></caption>

                <thead>
                    <tr>
                        <th>Transaction Type</th>
                        <th>Mode of Payment</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
    <?php if ($servicesFormatted === "Dine-In" || $servicesFormatted === "Take-Out"): ?>
        <tr>
    <td rowspan="4"><?php echo htmlspecialchars($servicesFormatted); ?></td>
    <td>Cash/Card</td>
    <td>₱ <?php echo number_format(isset($data['transactions']['Cash/Card']) ? $data['transactions']['Cash/Card'] : 0, 2); ?></td>
</tr>
<tr>
    <td>GCash</td>
    <td>₱ <?php echo number_format(isset($data['transactions']['GCash']) ? $data['transactions']['GCash'] : 0, 2); ?></td>
</tr>
<tr>
    <td>Paymaya</td>
    <td>₱ <?php echo number_format(isset($data['transactions']['Paymaya']) ? $data['transactions']['Paymaya'] : 0, 2); ?></td>
</tr>
<tr>
    <td>Other Sales</td>
    <td>₱ <?php echo number_format(isset($data['transactions']['Other Sales']) ? $data['transactions']['Other Sales'] : 0, 2); ?></td>
</tr>

    <?php else: ?>
        <tr>
    <td rowspan="3"><?php echo htmlspecialchars($servicesFormatted); ?></td>
    <td>GrabFood</td>
    <td>₱ <?php echo number_format(isset($data['transactions']['GrabFood']) ? $data['transactions']['GrabFood'] : 0, 2); ?></td>
</tr>
<tr>
    <td>FoodPanda</td>
    <td>₱ <?php echo number_format(isset($data['transactions']['FoodPanda']) ? $data['transactions']['FoodPanda'] : 0, 2); ?></td>
</tr>
<tr>
    <td>Other Sales</td>
    <td>₱ <?php echo number_format(isset($data['transactions']['Other Sales']) ? $data['transactions']['Other Sales'] : 0, 2); ?></td>
</tr>

    <?php endif; ?>
</tbody>
<tfoot>
    <tr>
        <td colspan="2" style="text-align: right;">Grand Total:</td>
        <td>₱ <?php echo number_format($data['grand_total'], 2); ?></td>
    </tr>
</tfoot>


                </tbody>
            </table>
        </div>
    </section>
                  
    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
        crossorigin="anonymous"></script>
    <script src="../../assets/js/navbar.js"></script>
    <script src="../../assets/js/display-sales-information-script.js"></script>
</body>

</html>
