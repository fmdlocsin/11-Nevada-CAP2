<?php
session_start();
include ("phpscripts/database-connection.php");
include ("phpscripts/check-login.php");

$yearlySalesData = []; // ✅ Ensure variable is always set

// Validate database connection
if (!isset($con) || !$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get filters from GET request
$franchisees = isset($_GET['franchisees']) ? explode(",", $_GET['franchisees']) : [];
$branches = isset($_GET['branches']) ? explode(",", $_GET['branches']) : [];
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Build dynamic WHERE clause
$whereClauses = [];
if (!empty($franchisees)) {
    $franchiseeList = "'" . implode("','", array_map(fn($f) => mysqli_real_escape_string($con, $f), $franchisees)) . "'";
    $whereClauses[] = "ac.franchisee IN ($franchiseeList)";
}
if (!empty($branches)) {
    $branchList = "'" . implode("','", array_map(fn($b) => mysqli_real_escape_string($con, $b), $branches)) . "'";
    $whereClauses[] = "ac.location IN ($branchList)";
}

// Apply date range filter
if (!empty($startDate) && !empty($endDate)) {
    $whereClauses[] = "sr.date_added BETWEEN '$startDate' AND '$endDate'";
}

// Only add WHERE clause if filters exist
$whereSQL = "";
if (!empty($whereClauses)) {
    $whereSQL = "WHERE " . implode(" AND ", $whereClauses);
}


// Update Sales Query with Date Filter
$salesQuery = "SELECT SUM(sr.grand_total) AS total_sales FROM sales_report sr 
               JOIN agreement_contract ac ON sr.ac_id = ac.ac_id $whereSQL";
$salesResult = mysqli_query($con, $salesQuery);
$totalSales = ($salesResult) ? mysqli_fetch_assoc($salesResult)['total_sales'] : 0;


// Only show debugging output when NOT in JSON mode
// if (!isset($_GET['json']) || $_GET['json'] !== "true") {
//     echo "<pre>Branch List Debug: "; print_r($branches); echo "</pre>";
// }

// Construct query for total expenses with date filter
$expensesQuery = "SELECT SUM(e.expense_amount) AS total_expenses 
                  FROM expenses e 
                  JOIN agreement_contract ac ON e.location = ac.ac_id";

// Prepare WHERE conditions
$expenseWhereClauses = [];

if (!empty($franchisees)) {
    $expenseFranchiseeList = "'" . implode("','", array_map(fn($f) => mysqli_real_escape_string($con, $f), $franchisees)) . "'";
    $expenseWhereClauses[] = "ac.franchisee IN ($expenseFranchiseeList)";
}

if (!empty($branches)) {
    // Convert branch names to ac_id before filtering
    $branchIdQuery = "SELECT ac_id FROM agreement_contract WHERE location IN ('" . implode("','", $branches) . "')";
    $branchIdResult = mysqli_query($con, $branchIdQuery);

    $branchIds = [];
    while ($row = mysqli_fetch_assoc($branchIdResult)) {
        $branchIds[] = $row['ac_id'];
    }

    if (!empty($branchIds)) {
        $expenseWhereClauses[] = "e.location IN ('" . implode("','", $branchIds) . "')";
    }
}

// Apply date range filter to expenses
if (!empty($startDate) && !empty($endDate)) {
    $expenseWhereClauses[] = "e.date_added BETWEEN '$startDate' AND '$endDate'";  // ✅ Added date filter here
}

if (!empty($expenseWhereClauses)) {
    $expensesQuery .= " WHERE " . implode(" AND ", $expenseWhereClauses);
}

// Execute query
$expensesResult = mysqli_query($con, $expensesQuery);
$totalExpenses = ($expensesResult) ? mysqli_fetch_assoc($expensesResult)['total_expenses'] : 0;


// Franchise Name Mapping
$franchise_name_map = [
    "auntie-anne" => "Auntie Anne's",
    "macao-imperial" => "Macao Imperial",
    "potato-corner" => "Potato Corner"
];

// Function to Format Franchise Name
function formatFranchiseName($franchise) {
    global $franchise_name_map;
    return $franchise_name_map[$franchise] ?? ucwords(str_replace("-", " ", $franchise));
}

// Sales Performance per Franchise (Pie Chart)
$sales_per_franchise_query = "SELECT ac.franchisee, SUM(sr.grand_total) AS total_sales 
                              FROM sales_report sr 
                              JOIN agreement_contract ac ON sr.ac_id = ac.ac_id 
                              $whereSQL GROUP BY ac.franchisee";
$sales_per_franchise_result = mysqli_query($con, $sales_per_franchise_query);

$franchise_sales_data = [];
if ($sales_per_franchise_result) {
    while ($row = mysqli_fetch_assoc($sales_per_franchise_result)) {
        $franchise_sales_data[] = [
            'franchise' => formatFranchiseName($row['franchisee']),
            'sales' => (float)$row['total_sales']
        ];
    }
}
$franchise_sales_json = json_encode($franchise_sales_data);

// Sales Performance per Branch (Pie Chart)
$sales_per_franchise_branch_query = "SELECT ac.franchisee, ac.location, SUM(sr.grand_total) AS total_sales
                                     FROM sales_report sr
                                     JOIN agreement_contract ac ON sr.ac_id = ac.ac_id
                                     $whereSQL GROUP BY ac.franchisee, ac.location
                                     ORDER BY ac.franchisee, total_sales DESC";
$sales_per_franchise_branch_result = mysqli_query($con, $sales_per_franchise_branch_query);

$franchise_branch_sales_data = [];
if ($sales_per_franchise_branch_result) {
    while ($row = mysqli_fetch_assoc($sales_per_franchise_branch_result)) {
        $formattedFranchise = formatFranchiseName($row['franchisee']);
        $franchise_branch_sales_data[$formattedFranchise][] = [
            'location' => $row['location'],
            'sales' => (float)$row['total_sales']
        ];
    }
}
$franchise_branch_sales_json = json_encode($franchise_branch_sales_data);

$best_selling_query = "SELECT sr.product_name, ac.franchisee, ac.location, SUM(sr.grand_total) AS total_sales
                       FROM sales_report sr
                       JOIN agreement_contract ac ON sr.ac_id = ac.ac_id";

$best_selling_whereClauses = [];

if (!empty($franchisees)) {
    $best_selling_whereClauses[] = "ac.franchisee IN ($franchiseeList)";
}

if (!empty($branches)) {
    $best_selling_whereClauses[] = "ac.location IN ($branchList)";
}

if (!empty($startDate) && !empty($endDate)) {
    $best_selling_whereClauses[] = "sr.date_added BETWEEN '$startDate' AND '$endDate'";
}

if (!empty($best_selling_whereClauses)) {
    $best_selling_query .= " WHERE " . implode(" AND ", $best_selling_whereClauses);
}

$best_selling_query .= " GROUP BY sr.product_name, ac.franchisee, ac.location
                        ORDER BY total_sales DESC LIMIT 5";

$best_selling_result = mysqli_query($con, $best_selling_query);

$best_selling_data = [];
if ($best_selling_result) {
    while ($row = mysqli_fetch_assoc($best_selling_result)) {
        $best_selling_data[] = [
            'product' => $row['product_name'],
            'franchise' => formatFranchiseName($row['franchisee']),
            'location' => $row['location'],
            'sales' => (float)$row['total_sales']
        ];
    }
}
$best_selling_json = json_encode($best_selling_data);


// Fetch Worst-Selling Products (Bottom 5)
$worst_selling_query = "SELECT sr.product_name, ac.franchisee, ac.location, SUM(sr.grand_total) AS total_sales
                        FROM sales_report sr
                        JOIN agreement_contract ac ON sr.ac_id = ac.ac_id";

$worst_selling_whereClauses = [];

if (!empty($franchisees)) {
    $worst_selling_whereClauses[] = "ac.franchisee IN ($franchiseeList)";
}

if (!empty($branches)) {
    $worst_selling_whereClauses[] = "ac.location IN ($branchList)";
}

if (!empty($startDate) && !empty($endDate)) {
    $worst_selling_whereClauses[] = "sr.date_added BETWEEN '$startDate' AND '$endDate'";
}

if (!empty($worst_selling_whereClauses)) {
    $worst_selling_query .= " WHERE " . implode(" AND ", $worst_selling_whereClauses);
}

$worst_selling_query .= " GROUP BY sr.product_name, ac.franchisee, ac.location
                         ORDER BY total_sales ASC LIMIT 5";

$worst_selling_result = mysqli_query($con, $worst_selling_query);

$worst_selling_data = [];
if ($worst_selling_result) {
    while ($row = mysqli_fetch_assoc($worst_selling_result)) {
        $worst_selling_data[] = [
            'product' => $row['product_name'],
            'franchise' => formatFranchiseName($row['franchisee']),
            'location' => $row['location'],
            'sales' => (float)$row['total_sales']
        ];
    }
}
$worst_selling_json = json_encode($worst_selling_data);


// Fetch all franchisees
$franchiseeQuery = "SELECT DISTINCT franchisee FROM agreement_contract";
$franchiseeResult = mysqli_query($con, $franchiseeQuery);
$franchisees = mysqli_fetch_all($franchiseeResult, MYSQLI_ASSOC);

// Fetch all branches (filtered by franchise if selected)
$branches = [];

if (!empty($franchiseeList)) {
    $branchQuery = "
        SELECT DISTINCT ac.location AS branch_id, ac.location AS branch_name
        FROM agreement_contract ac
        WHERE ac.franchisee IN ($franchiseeList)

        UNION

        SELECT DISTINCT ac.location AS branch_id, ac.location AS branch_name
        FROM sales_report sr
        JOIN agreement_contract ac ON sr.ac_id = ac.ac_id
        WHERE ac.franchisee IN ($franchiseeList)

        UNION

        SELECT DISTINCT 
            COALESCE(ac.location, e.location) AS branch_id, 
            COALESCE(ac.location, e.location) AS branch_name
        FROM expenses e
        LEFT JOIN agreement_contract ac ON e.location = ac.ac_id
        WHERE e.franchisee IN ($franchiseeList);
    ";

    $branchResult = mysqli_query($con, $branchQuery);

    if (!$branchResult) {
        die("Branch Query Failed: " . mysqli_error($con));
    }

    while ($row = mysqli_fetch_assoc($branchResult)) {
        $branches[] = [
            "branch" => $row['branch_name'],
            "location" => $row['branch_id']
        ];
    }
}

// ✅ Start building the Yearly Sales Trend query
$yearlySalesQuery = "SELECT YEAR(sr.date_added) AS sales_year, SUM(sr.grand_total) AS total_sales
                     FROM sales_report sr
                     JOIN agreement_contract ac ON sr.ac_id = ac.ac_id";

// ✅ Add filtering
$yearlyWhereClauses = [];

if (!empty($franchiseeList)) {
    $yearlyWhereClauses[] = "ac.franchisee IN ($franchiseeList)";
}

if (!empty($branchList)) {
    $yearlyWhereClauses[] = "ac.location IN ($branchList)";
}

// ✅ Only add WHERE clause if there are filters
if (!empty($yearlyWhereClauses)) {
    $yearlySalesQuery .= " WHERE " . implode(" AND ", $yearlyWhereClauses);
}

$yearlySalesQuery .= " GROUP BY sales_year ORDER BY sales_year ASC";

// ✅ Now execute the final query **after** filters have been applied
$yearlySalesResult = mysqli_query($con, $yearlySalesQuery);

// ✅ Debug: Check if the query executed successfully
if (!$yearlySalesResult) {
    die(json_encode(["error" => "Yearly Sales Query Failed: " . mysqli_error($con)]));
}

// ✅ Fetch results and store in array
$yearlySalesData = [];
while ($row = mysqli_fetch_assoc($yearlySalesResult)) {
    $yearlySalesData[] = [
        'year' => (int) $row['sales_year'],
        'sales' => (float) $row['total_sales']
    ];
}


// Return JSON if requested
if (isset($_GET['json']) && $_GET['json'] == "true") {
    header("Content-Type: application/json");
    echo json_encode([
        "totalSales" => floatval($totalSales) ?: 0,  // ✅ Ensures number format
        "totalExpenses" => floatval($totalExpenses) ?: 0,  // ✅ Ensures number format
        "profit" => $totalSales - $totalExpenses,
        "franchisees" => $franchisees,
        "branches" => $branches,
        "franchiseSales" => $franchise_sales_data,
        "branchSales" => $franchise_branch_sales_data,
        "bestSelling" => $best_selling_data,
        "worstSelling" => $worst_selling_data,
        "yearlySalesTrend" => $yearlySalesData
    ]);
    exit();
}
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

    <div class="content" id="content-area">
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

                    <span class="filter-label">Filter:</span>
                    <div id="filters" class="filter-section">
                        

                        <!-- Franchisee Filter Buttons -->
                        <div id="franchiseeButtons" class="filter-buttons">
                            <h4>Select Franchisee:</h4>
                        </div>

                        <!-- Branch Filter Buttons (Now Directly Below) -->
                        <div id="branchButtons" class="filter-buttons" style="display: none;">
                            <h4>Select Branch:</h4>
                        </div>

                        <div class="filter-section2">
                            <label for="startDate">Start Date:</label>
                            <input type="date" id="startDate" class="form-control" onchange="fetchKPIData()">
                            
                            <label for="endDate">End Date:</label>
                            <input type="date" id="endDate" class="form-control" onchange="fetchKPIData()">

                            <button class="btn btn-primary" onclick="generateReport()">Generate Report</button>
                        </div>
                    </div>


                        <!-- NEW KPI CARDS -->
                        <div class="row kpi-row">
                            <!-- Total Sales -->
                            <div class="col-md-4 kpi-col">
                                <a href="pages/salesPerformance/chooseFranchisee" class="card kpi-card kpi-link">
                                    <div class="card-body">
                                        <h2 class="kpi-number" id="totalSales">0</h2>
                                        <h4 class="kpi-label">Total Sales</h4>
                                    </div>
                                </a>
                            </div>

                            <!-- Total Expenses -->
                            <div class="col-md-4 kpi-col">
                                <a href="pages/salesPerformance/totalExpenses" class="card kpi-card kpi-link">
                                    <div class="card-body">
                                        <h2 class="kpi-number" id="totalExpenses">0</h2>
                                        <h4 class="kpi-label">Total Expenses</h4>
                                    </div>
                                </a>
                            </div>

                            <!-- Profit (Non-Clickable) -->
                            <div class="col-md-4 kpi-col">
                                <div class="card kpi-card">
                                    <div class="card-body">
                                        <h2 class="kpi-number" id="profit">0</h2>
                                        <h4 class="kpi-label">Profit</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                     <!-- Summary Boxes (Still Inside .boxes)
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
                        </div> -->

                        <!-- NEW: Charts are now placed BELOW the summary boxes -->
                        <div class="charts-container">
                            <!-- Left Column for Franchise Sales and Yearly Sales Trend -->
                            <div class="charts-column">
                                <!-- Yearly Sales Trend Chart -->
                                <div class="chart-box-yearly">
                                        <h2>Yearly Sales Trend</h2>
                                        <div class="chart-container">
                                            <canvas id="yearlySalesChart"></canvas>
                                        </div>
                                    </div>
                                    
                                    <!-- Franchise Sales Chart -->
                                    <div class="chart-box">
                                        <h2>Sales Performance per Franchise</h2>
                                        <div class="chart-container">
                                            <canvas id="franchiseSalesChart"></canvas>
                                        </div>
                                        <div id="franchiseLegend" class="chart-legend"></div>
                                    </div>
                                </div>

                            <!-- Right Section: Franchise & Branch Sales + Best/Worst Selling Charts -->
                            <div class="right-content">
                                <div class="chart-box" id="franchiseBranchChartContainer">
                                    <h2>Sales Performance by Franchise & Branch</h2>
                                    <div id="franchiseCheckboxes"></div>
                                    <canvas id="franchiseBranchChart"></canvas>
                                    <div id="branchLegend" class="chart-legend"></div>
                                </div>

                                <!-- Bar Charts & Checkboxes Container -->
                                <div class="bar-charts-wrapper">

                                    <!-- Bar Charts Container -->
                                    <div class="bar-charts-container">
                                        <div class="chart-box small-chart">
                                            <h2>Top 5 Best-Selling Products</h2>
                                            <canvas id="bestSellingChart"></canvas>
                                            <div id="bestSellingLegend" class="chart-legend"></div> <!-- ✅ Legend Added -->
                                        </div>
                                        <div class="chart-box small-chart">
                                            <h2>Top 5 Worst-Selling Products</h2>
                                            <canvas id="worstSellingChart"></canvas>
                                            <div id="worstSellingLegend" class="chart-legend"></div> <!-- ✅ Legend Added -->
                                        </div>
                                    </div>
                                

                                <!-- Report Modal -->
                                <div id="reportModal" class="modal fade" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title">Sales Report</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                
                                                <!-- 🔍 Selected Filters -->
                                                <div class="alert alert-secondary">
                                                    <h6><strong>Applied Filters:</strong></h6>
                                                    <p><strong>Franchisee(s):</strong> <span id="selectedFranchisees">All</span></p>
                                                    <p><strong>Branch(es):</strong> <span id="selectedBranches">All</span></p>
                                                    <p><strong>Date Range:</strong> <span id="selectedDateRange">Not Set</span></p>
                                                </div>

                                                <!-- Report Type Buttons -->
                                                <div class="btn-group">
                                                    <button class="btn btn-primary report-btn" onclick="fetchReport('daily')">Daily</button>
                                                    <button class="btn btn-primary report-btn" onclick="fetchReport('weekly')">Weekly</button>
                                                    <button class="btn btn-primary report-btn" onclick="fetchReport('monthly')">Monthly</button>
                                                </div>


                                                <!-- Report Table -->
                                                <div class="table-responsive mt-3">
                                                    <table id="reportTable" class="table table-bordered table-hover table-striped">
                                                        <!-- <thead class="table-dark">
                                                            <tr>
                                                                <th>Date</th>
                                                                <th>Franchisee</th>
                                                                <th>Branch</th>
                                                                <th>Product</th>
                                                                <th class="text-end">Total Sales</th>
                                                                <th class="text-end">Total Expenses</th>
                                                                <th class="text-end">Profit</th>
                                                            </tr>
                                                        </thead> -->
                                                        <tbody id="reportTableBody">
                                                            <!-- Data will be inserted here -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button class="btn btn-success" onclick="exportTableToCSV()">Export as CSV</button>
                                                <button class="btn btn-danger" onclick="exportTableToPDF()">Export as PDF</button>
                                            </div>

                                        </div>
                                    </div>
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

    <script>
        document.getElementById("endDate").addEventListener("change", function() {
            let startDate = new Date(document.getElementById("startDate").value);
            let endDate = new Date(document.getElementById("endDate").value);

            if (startDate > endDate) {
                alert("End Date cannot be earlier than Start Date!");
                document.getElementById("endDate").value = "";
            }
        });
    </script>




<!-- Debugging: Check if JSON Data is Outputted -->
<script id="locationSalesData" type="application/json">
    <?php echo isset($location_sales_json) ? $location_sales_json : '[]'; ?>
</script>

<!-- Ensure this is below the JSON output -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script src="assets/js/salesAnalytics.js"></script> 

<!-- Export to PDF  -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>



</body>

</html>