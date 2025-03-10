<?php
session_start();

include ("../../phpscripts/database-connection.php");
include ("../../phpscripts/check-login.php");

$queryString = $_SERVER['QUERY_STRING'];
parse_str(str_replace('/', '&', $queryString), $queryParams);

$eatType = isset($queryParams['tp']) ? mysqli_real_escape_string($con, $queryParams['tp']) : '';
$franchise = isset($queryParams['franchise']) ? mysqli_real_escape_string($con, $queryParams['franchise']) : '';
$location = isset($queryParams['location']) ? mysqli_real_escape_string($con, $queryParams['location']) : '';


$franchiseFormattedMap = [
    "PotatoCorner" => "Potato Corner",
    "MacaoImperial" => "Macao Imperial",
    "AuntieAnne" => "Auntie Anne's"
];

$eatTypeFormattedMap = [
    "DineIn" => "Dine-In",
    "TakeOut" => "Take-Out",
    "Delivery" => "Delivery"
];

$franchiseFormatted = isset($franchiseFormattedMap[$franchise]) ? $franchiseFormattedMap[$franchise] : $franchise;
$eatTypeFormatted = isset($eatTypeFormattedMap[$eatType]) ? $eatTypeFormattedMap[$eatType] : $eatType;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sales Reports</title>

    <!-- ========= CSS ========= -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../../assets/css/navbar.css">
    <link rel="stylesheet" href="../../assets/css/salesTransactions.css">

    <!-- ===== Boxicons CSS ===== -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    
    <?php include '../../navbar.php'; ?>

    <section class="home">
        <header class="contractheader">
            <div class="container-header">
                <h1 class="title"><?php echo $eatTypeFormatted; ?> Sales - <?php echo $franchiseFormatted; ?></h1>
            </div>
        </header>
        <div class="filter-container">
            <!-- Filters -->
            <div class="filters">
                <!-- <label for="filter-franchise">Franchisee:</label>
                <select id="filter-franchise">
                    <option value="">All</option>
                    <option value="potato-corner">Potato Corner</option>
                    <option value="auntie-anne">Auntie Anne's</option>
                    <option value="macao-imperial">Macao Imperial</option>
                </select> -->

                <!-- <label for="filter-status">Location:</label>
                <select id="filter-status">
                    <option value="">All</option>
                    <option value="approved">location 1</option>
                    <option value="pending">Pending</option>
                    <option value="leasing">Leasing</option>
                </select>

                <label for="filter-status">Merchant:</label>
                <select id="filter-status">
                    <option value="">All</option>
                    <option value="foodpanda">foodpanda</option>
                    <option value="grabfood">GrabFood</option>
                </select> -->


                <label for="start-date">Start Date:</label>
                <input type="date" id="start-date">

                <label for="end-date">End Date:</label>
                <input type="date" id="end-date">


                <button id="btn-generate" class="resetButton">Generate</button>
                <!-- <button id="btn-reset" class="resetButton">Reset</button> -->

                <!-- Encode Sales Report -->
                <a href="encodeSales?tp=<?php echo $eatType ?>&franchise=<?php echo $franchise ?>&location=<?php echo urlencode($location); ?>"
                class="myButton">Encode Sales Report</a>



                <!-- Upload File Button
            <label for="file-upload" class="myButton">Upload File</label>
            <input type="file" id="file-upload" style="display: none;"> -->


            </div>
        </div>
        <div class="container">
            <section id="delivery-section">
                <table class="content-table" id="salesReportTbl">
                    <thead>
                        <tr>
                            <th>Franchisee</th>
                            <th>Location</th>
                            <th>Total Sales</th>
                            <th>Transaction Type</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </section>
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