<?php
session_start();

include ("phpscripts/database-connection.php");
include ("phpscripts/check-login.php");

// Expenses
$expenses_query = "SELECT SUM(expense_amount) AS total_expenses FROM expenses";
$expenses_result = mysqli_query($con, $expenses_query);

$sales_query = "SELECT SUM(grand_total) AS total_sales FROM sales_report";
$sales_result = mysqli_query($con, $sales_query);

$totalExpenses = 0;
$totalSales = 0;

if ($expenses_result || $sales_result) {
    $sales_row = mysqli_fetch_assoc($sales_result);
    $expenses_row = mysqli_fetch_assoc($expenses_result);

    $totalSales = $sales_row['total_sales'];
    $totalExpenses = $expenses_row['total_expenses'];
} else {
    $error = "Database query failed: " . mysqli_error($con);
}


// Define mappings for franchise name formatting
$franchise_name_map = [
    "auntie-anne" => "Auntie Anne's",
    "macao-imperial" => "Macao Imperial",
    "potato-corner" => "Potato Corner"
];


// Sales Performance per Franchise (Pie Chart)
$sales_per_franchise_query = "SELECT franchisee, SUM(grand_total) AS total_sales FROM sales_report GROUP BY franchisee";
$sales_per_franchise_result = mysqli_query($con, $sales_per_franchise_query);

$franchise_sales_data = [];

if ($sales_per_franchise_result) {
    while ($row = mysqli_fetch_assoc($sales_per_franchise_result)) {
        $raw_franchise_name = $row['franchisee'];
        
        // Format the franchise name
        $formatted_franchise_name = isset($franchise_name_map[$raw_franchise_name])
            ? $franchise_name_map[$raw_franchise_name]
            : ucwords(str_replace("-", " ", $raw_franchise_name)); // Fallback for unknown names

        $franchise_sales_data[] = [
            'franchise' => $formatted_franchise_name, // Use formatted name
            'sales' => (float)$row['total_sales']
        ];
    }
}

$franchise_sales_json = json_encode($franchise_sales_data);


// Sales Performance per Branch (Pie Chart)
$sales_per_franchise_branch_query = "
    SELECT ac.franchisee, ac.location, SUM(sr.grand_total) AS total_sales
    FROM sales_report sr
    JOIN agreement_contract ac ON sr.ac_id = ac.ac_id
    GROUP BY ac.franchisee, ac.location
    ORDER BY ac.franchisee, total_sales DESC
";

$sales_per_franchise_branch_result = mysqli_query($con, $sales_per_franchise_branch_query);

$franchise_branch_sales_data = [];

if ($sales_per_franchise_branch_result) {
    while ($row = mysqli_fetch_assoc($sales_per_franchise_branch_result)) {
        $raw_franchise_name = $row['franchisee'];
        $formatted_franchise_name = isset($franchise_name_map[$raw_franchise_name])
            ? $franchise_name_map[$raw_franchise_name]
            : ucwords(str_replace("-", " ", $raw_franchise_name)); // Fallback for unknown names

        $location = $row['location'];
        $sales = (float)$row['total_sales'];

        if (!isset($franchise_branch_sales_data[$formatted_franchise_name])) {
            $franchise_branch_sales_data[$formatted_franchise_name] = [];
        }

        $franchise_branch_sales_data[$formatted_franchise_name][] = [
            'location' => $location,
            'sales' => $sales
        ];
    }
}

$franchise_branch_sales_json = json_encode($franchise_branch_sales_data);


// Fetch Best-Selling Products (Top 5)
$best_selling_query = "
    SELECT product_name, SUM(grand_total) AS total_sales
    FROM sales_report
    GROUP BY product_name
    ORDER BY total_sales DESC
    LIMIT 5";
$best_selling_result = mysqli_query($con, $best_selling_query);

$best_selling_data = [];

if ($best_selling_result) {
    while ($row = mysqli_fetch_assoc($best_selling_result)) {
        $best_selling_data[] = [
            'product' => $row['product_name'],
            'sales' => (float)$row['total_sales']
        ];
    }
}

// Fetch Worst-Selling Products (Bottom 5)
$worst_selling_query = "
    SELECT product_name, SUM(grand_total) AS total_sales
    FROM sales_report
    GROUP BY product_name
    ORDER BY total_sales ASC
    LIMIT 5";
$worst_selling_result = mysqli_query($con, $worst_selling_query);

$worst_selling_data = [];

if ($worst_selling_result) {
    while ($row = mysqli_fetch_assoc($worst_selling_result)) {
        $worst_selling_data[] = [
            'product' => $row['product_name'],
            'sales' => (float)$row['total_sales']
        ];
    }
}

// Convert to JSON for JavaScript
$best_selling_json = json_encode($best_selling_data);
$worst_selling_json = json_encode($worst_selling_data);



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- ========= CSS ========= -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <!-- <link rel="stylesheet" href="assets/css/dashboard.css"> -->
    <link rel="stylesheet" href="assets/css/expenses.css">
    <link rel="stylesheet" href="assets/css/salesAnalytics.css">
    <!-- ===== Boxicons CSS ===== -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>


</head>

<body>

    <nav class="sidebar close">
        <header>
            <div class="image-text">
                <span class="image">
                    <img src="assets/images/BoxLogo.png" alt="logo">
                </span>

                <div class="text header-text">
                    <span class="name">NEVADA</span>
                    <span class="profession">Management Group</span>
                </div>
            </div>

            <i class='bx bx-chevron-right toggle'></i>
        </header>
        <div class="menu-bar">
            <div class="menu">
                <li class="search-box">
                    <i class='bx bx-search icon'></i>
                    <input type="search" placeholder="Search...">
                </li>
                <ul class="menu-links">
                    <li class="nav-link active" id="dashboard-link">
                        <a href="dashboard-sales">
                            <i class='bx bx-home-alt icon'></i>
                            <span class="text nav-text">Dashboard</span>
                        </a>
                    </li>
                  
                    <li class="nav-link" id="sales-link">
                        <a href="pages/salesPerformance/sales">
                            <i class='bx bx-bar-chart-alt-2 icon'></i>
                            <span class="text nav-text">Sales Performance</span>
                        </a>
                    </li>
                    <li class="nav-link" id="expenses-link">
                        <a href="pages/salesPerformance/totalExpenses">
                            <i class='bx bx-wallet icon'></i>
                            <span class="text nav-text">Expenses</span>
                        </a>
                    </li>

                </ul>
            </div>

            <div class="bottom-content">
                <li>
                    <a href="phpscripts/user-logout.php">
                        <i class='bx bx-log-out icon'></i>
                        <span class="text nav-text">Logout</span>
                    </a>
                </li>
            </div>

        </div>
    </nav>

    <section class="home">
        <header class="contractheader">
            <div class="container-header">
                <h1 class="title">Dashboard</h1>
            </div>
        </header>
        <div class="container">
            <div class="dash-content">
                <div class="overview">
                <div class="greeting">
                            <h2>Hi, <strong>Group/Branch Manager</strong>!</h2>
                        </div>
                    <div class="title">
                        <i class='bx bx-time-five'></i>
                        <span class="text">Analytics</span>
                    </div>
                     <!-- Summary Boxes (Still Inside .boxes) -->
                        <div class="boxes">
                            <a href="pages/salesPerformance/totalExpenses" class="box box1">
                                <span class="text1"><?php echo number_format($totalExpenses, 2) ?></span>
                                <span class="text">Total Expenses</span>
                            </a>
                            <a href="pages/salesPerformance/chooseFranchisee" class="box box2">
                                <span class="text1"><?php echo number_format($totalSales, 2) ?></span>
                                <span class="text">Total Sales</span>
                            </a>
                            <div class="box box3">
                                <span class="text1"><?php echo number_format($totalSales - $totalExpenses, 2) ?></span>
                                <span class="text">Profit</span>
                            </div>
                        </div>

                        <!-- 🔴 NEW: Charts are now placed BELOW the summary boxes -->
                        <div class="charts-container">
                            <!-- Franchise Sales Chart -->
                            <div class="chart-box">
                                <h2>Sales Performance per Franchise</h2>
                                <div class="chart-container">
                                    <canvas id="franchiseSalesChart"></canvas>
                                </div>
                                <div id="franchiseLegend" class="chart-legend"></div>
                            </div>

                            <!-- Right Section: Franchise & Branch Sales + Best/Worst Selling Charts -->
                            <div class="right-content">
                                <div class="chart-box" id="franchiseBranchChartContainer">
                                    <h2>Sales Performance by Franchise & Branch</h2>
                                    <div id="franchiseCheckboxes"></div>
                                    <canvas id="franchiseBranchChart"></canvas>
                                    <div id="branchLegend" class="chart-legend"></div>
                                </div>

                                <!-- Bar Charts: Placed in the extra space beside the pie chart -->
                                <div class="bar-charts-container">
                                    <div class="chart-box small-chart">
                                        <h2>Top 5 Best-Selling Products</h2>
                                        <canvas id="bestSellingChart"></canvas>
                                    </div>
                                    <div class="chart-box small-chart">
                                        <h2>Top 5 Worst-Selling Products</h2>
                                        <canvas id="worstSellingChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    </div>
                </div>
            </div>
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
    <script src="assets/js/navbar.js"></script>

    <!-- Store franchise sales data as JSON -->
    <script id="franchiseSalesData" type="application/json">
        <?php echo $franchise_sales_json; ?>
    </script>

    <!-- Store location sales data as JSON -->
    <script id="franchiseBranchSalesData" type="application/json">
        <?php echo $franchise_branch_sales_json; ?>
    </script>

    <script id="bestSellingData" type="application/json">
        <?php echo $best_selling_json; ?>
    </script>

    <script id="worstSellingData" type="application/json">
        <?php echo $worst_selling_json; ?>
    </script>




<!-- Debugging: Check if JSON Data is Outputted -->
<script id="locationSalesData" type="application/json">
    <?php echo isset($location_sales_json) ? $location_sales_json : '[]'; ?>
</script>

<!-- Ensure this is below the JSON output -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script src="assets/js/salesAnalytics.js"></script> 


</body>

</html>