<?php
session_start();
include ("phpscripts/database-connection.php");
include ("phpscripts/check-login.php");



// ✅ Prevent PHP errors from displaying in the AJAX response
ini_set('log_errors', 1);
ini_set('display_errors', 0);
error_reporting(E_ALL);

// ✅ Franchise selection: Fetch branches
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['franchise'])) {
    header('Content-Type: application/json; charset=utf-8'); // ✅ Ensure JSON response

    if (!isset($con) || !$con) {
        echo json_encode(["error" => "Database connection failed: " . mysqli_connect_error()]);
        exit;
    }

    // ✅ Decode JSON Array of Franchises
    $franchises = json_decode($_POST['franchise'], true);

    if (!is_array($franchises) || empty($franchises)) {
        echo json_encode(["branches" => [], "clear" => true]); // ✅ Include clear flag
        exit;
    }
    

    // ✅ Franchise Name Mapping
    $franchiseMap = [
        "Potato Corner" => "potato-corner",
        "Auntie Anne's" => "auntie-anne",
        "Macao Imperial Tea" => "macao-imperial"
    ];

    // ✅ Convert to Database Format
    $dbFranchises = array_map(fn($f) => $franchiseMap[$f] ?? null, $franchises);
    $dbFranchises = array_filter($dbFranchises); // Remove any nulls

    if (empty($dbFranchises)) {
        echo json_encode(["branches" => []]);
        exit;
    }

    // ✅ Dynamic Query for Multiple Franchises
    $placeholders = implode(",", array_fill(0, count($dbFranchises), "?"));
    $query = "SELECT DISTINCT branch FROM item_inventory WHERE franchisee IN ($placeholders)";

    $stmt = $con->prepare($query);
    if (!$stmt) {
        echo json_encode(["error" => "SQL Error: " . $con->error]);
        exit;
    }

    // ✅ Bind Parameters Dynamically
    $stmt->bind_param(str_repeat("s", count($dbFranchises)), ...$dbFranchises);
    $stmt->execute();
    $result = $stmt->get_result();

    $branches = [];
    while ($row = $result->fetch_assoc()) {
        $branches[] = $row['branch'];
    }

    echo json_encode(["branches" => $branches], JSON_UNESCAPED_UNICODE);
    exit;
}


// ✅ Branch selection: Fetch inventory data
// ✅ Branch selection: Fetch inventory data
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['branches'])) {
    header('Content-Type: application/json; charset=utf-8'); // ✅ Ensure JSON response

    if (!isset($con) || !$con) {
        echo json_encode(["error" => "Database connection failed: " . mysqli_connect_error()]);
        exit;
    }

    // ✅ Decode JSON array from AJAX request
    $branches = json_decode(trim($_POST['branches']), true);

    // ✅ Ensure it's a valid array and not empty
    if (!is_array($branches) || empty($branches)) {
        echo json_encode([
            "stock_level" => 0,
            "stockout_count" => 0,
            "total_wastage" => 0,
            "high_turnover" => ["labels" => [], "values" => []],
            "low_turnover" => ["labels" => [], "values" => []],
            "sell_through_rate" => ["dates" => [], "values" => []]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
        // ✅ Default start and end date to the current week (Monday - Sunday)
    $startDate = $_POST["startDate"] ?? date("Y-m-d", strtotime("monday this week"));
    $endDate = $_POST["endDate"] ?? date("Y-m-d", strtotime("sunday this week"));

    // ✅ Dynamic query for multiple branches
    $placeholders = implode(",", array_fill(0, count($branches), "?"));

    // ✅ Fetch inventory KPIs
    $query = "SELECT 
            SUM(beginning - sold - waste) AS stock_level,
            COUNT(CASE WHEN (beginning - sold - waste) = 0 THEN 1 END) AS stockout_count,
            SUM(waste) AS total_wastage
            FROM item_inventory 
            WHERE branch IN ($placeholders) 
            AND DATE(datetime_added) BETWEEN ? AND ?";

   
   
$stmt = $con->prepare($query);
if (!$stmt) {
    echo json_encode(["error" => "SQL Prepare Failed: " . $con->error]);
    exit;
}

$types = str_repeat("s", count($branches)) . "ss"; // ✅ Generate the correct types
$params = array_merge($branches, [$startDate, $endDate]); // ✅ Merge all parameters

$stmt->bind_param($types, ...$params); // ✅ Fix: No positional arguments after unpacking
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

    // ✅ Fetch top 5 high turnover items for selected branches
    $highTurnoverQuery = "SELECT i.item_name, 
                (SUM(ii.sold) / NULLIF(SUM(ii.beginning + ii.delivery - ii.waste), 0)) AS turnover_rate 
                FROM item_inventory ii 
                INNER JOIN items i ON ii.item_id = i.item_id 
                WHERE ii.branch IN ($placeholders) 
                AND DATE(ii.datetime_added) BETWEEN ? AND ?
                GROUP BY i.item_name 
                ORDER BY turnover_rate DESC 
                LIMIT 5";


$stmtHigh = $con->prepare($highTurnoverQuery);
if (!$stmtHigh) {
    echo json_encode(["error" => "SQL Prepare Failed: " . $con->error]);
    exit;
}

$types = str_repeat("s", count($branches)) . "ss"; // ✅ Generate correct types string
$params = array_merge($branches, [$startDate, $endDate]); // ✅ Merge branch values with date range

$stmtHigh->bind_param($types, ...$params); // ✅ Fix: No positional arguments after unpacking
$stmtHigh->execute();
$highTurnoverResult = $stmtHigh->get_result();                                  

    $highTurnover = ["labels" => [], "values" => []];
    while ($row = $highTurnoverResult->fetch_assoc()) {
        $highTurnover["labels"][] = $row["item_name"];
        $highTurnover["values"][] = floatval($row["turnover_rate"]);
    }

    // ✅ Fetch top 5 low turnover items for selected branches
    $lowTurnoverQuery = "SELECT i.item_name, 
                (SUM(ii.sold) / NULLIF(SUM(ii.beginning + ii.delivery - ii.waste), 0)) AS turnover_rate 
                FROM item_inventory ii 
                INNER JOIN items i ON ii.item_id = i.item_id 
                WHERE ii.branch IN ($placeholders) 
                AND DATE(ii.datetime_added) BETWEEN ? AND ?
                GROUP BY i.item_name 
                ORDER BY turnover_rate ASC 
                LIMIT 5";


$stmtLow = $con->prepare($lowTurnoverQuery); // ✅ Correct variable name
if (!$stmtLow) {
    echo json_encode(["error" => "SQL Prepare Failed: " . $con->error]);
    exit;
}

$types = str_repeat("s", count($branches)) . "ss"; // ✅ Generate correct types string
$params = array_merge($branches, [$startDate, $endDate]); // ✅ Merge branch values with date range

$stmtLow->bind_param($types, ...$params); // ✅ Fix: No positional arguments after unpacking
$stmtLow->execute();
$lowTurnoverResult = $stmtLow->get_result();

    $lowTurnover = ["labels" => [], "values" => []];
    while ($row = $lowTurnoverResult->fetch_assoc()) {
        $lowTurnover["labels"][] = $row["item_name"];
        $lowTurnover["values"][] = floatval($row["turnover_rate"]);
    }

    // ✅ Fetch Sell-Through Rate Data for multiple branches
    $startDate = $_POST["startDate"] ?? date("Y-m-d", strtotime("-30 days"));
    $endDate = $_POST["endDate"] ?? date("Y-m-d");

    $sellThroughQuery = "SELECT DATE(datetime_added) AS sale_date, 
                    (SUM(sold) / NULLIF(SUM(delivery), 0)) * 100 AS sell_through_rate
                    FROM item_inventory 
                    WHERE branch IN ($placeholders) 
                    AND DATE(datetime_added) BETWEEN ? AND ?
                    GROUP BY DATE(datetime_added)
                    ORDER BY sale_date ASC";


    $stmtSellThrough = $con->prepare($sellThroughQuery);
    if (!$stmtSellThrough) {
        echo json_encode(["error" => "SQL Prepare Failed: " . $con->error]);
        exit;
    }

    // ✅ Fix bind_param: Merge all parameters into a single array
    $params = array_merge(array_fill(0, count($branches), "s"), ["s", "s"]); // Format string
    $values = array_merge($branches, [$startDate, $endDate]); // Merge branch values with dates

    // ✅ Dynamically bind all parameters
    $stmtSellThrough->bind_param(implode("", $params), ...$values);
    $stmtSellThrough->execute();
    $sellThroughResult = $stmtSellThrough->get_result();

    $sellThroughRate = ["dates" => [], "values" => []];
    while ($row = $sellThroughResult->fetch_assoc()) {
        $sellThroughRate["dates"][] = $row["sale_date"];
        $sellThroughRate["values"][] = floatval($row["sell_through_rate"]);
    }


    // ✅ Send JSON Response
    echo json_encode([
        "stock_level" => $data["stock_level"] ?? 0,
        "stockout_count" => $data["stockout_count"] ?? 0,
        "total_wastage" => $data["total_wastage"] ?? 0,
        "high_turnover" => $highTurnover,
        "low_turnover" => $lowTurnover,
        "sell_through_rate" => $sellThroughRate
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// the thingy for report generation
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // ✅ Map franchise names to match the database format
$franchiseMap = [
    "Potato Corner" => "potato-corner",
    "Auntie Anne's" => "auntie-anne",
    "Macao Imperial Tea" => "macao-imperial"
];

// ✅ Convert input franchise names to database format
$franchisees = isset($_POST["franchisees"]) ? array_map(fn($f) => $franchiseMap[$f] ?? $f, $_POST["franchisees"]) : [];

    $branches = isset($_POST["branches"]) ? $_POST["branches"] : [];
    $startDate = $_POST["startDate"] ?? "2000-01-01"; // Default to all-time
    $endDate = $_POST["endDate"] ?? date("Y-m-d"); // Default to today

    if (!isset($con) || !$con) {
        echo json_encode(["error" => "Database connection failed: " . mysqli_connect_error()]);
        exit;
    }

    // ✅ Build SQL Query
    $query = "SELECT 
        i.item_name,
        (SUM(ii.sold) / NULLIF(SUM(ii.delivery), 0)) * 100 AS sell_through_rate,
        (SUM(ii.beginning + ii.delivery - ii.sold - ii.waste) / NULLIF(AVG(ii.sold), 0)) AS days_until_stockout,
        AVG(ii.sold) AS average_sales,
        SUM(ii.waste) AS stock_waste
    FROM item_inventory ii
    INNER JOIN items i ON ii.item_id = i.item_id
    WHERE 1=1";

    // ✅ Fix Franchisee Filtering
    if (!empty($franchisees)) {
        $query .= " AND ii.franchisee IN (" . implode(',', array_map(fn($f) => "'$f'", $franchisees)) . ")";
    }

    // ✅ Fix Branch Filtering
    if (!empty($branches)) {
        $query .= " AND ii.branch IN (" . implode(',', array_map(fn($b) => "'$b'", $branches)) . ")";
    }

    // ✅ Always Apply Date Range (Defaults to "All Time" if no selection)
    $query .= " AND DATE(ii.datetime_added) BETWEEN '$startDate' AND '$endDate'";

    $query .= " GROUP BY i.item_name ORDER BY sell_through_rate DESC";

    // ✅ Debugging Output
    //echo json_encode(["query" => $query, "franchisees" => $franchisees, "branches" => $branches, "startDate" => $startDate, "endDate" => $endDate]);

    $result = mysqli_query($con, $query);
    if (!$result) {
        echo json_encode(["error" => "SQL Error: " . mysqli_error($con)]);
        exit;
    }

    $reportData = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $reportData[] = [
            "item_name" => $row["item_name"],
            "sell_through_rate" => round($row["sell_through_rate"], 2) . "%",
            "days_until_stockout" => round($row["days_until_stockout"], 1),
            "average_sales" => round($row["average_sales"], 2),
            "stock_waste" => round($row["stock_waste"], 2)
        ];
    }

    if (empty($reportData)) {
        echo json_encode(["data" => [], "message" => "No matching records found."], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["data" => $reportData], JSON_UNESCAPED_UNICODE);
    }
    exit;
    
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS (Ensure it's the bundle version) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/inventory-dashboard.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <title>Inventory Analytics</title>

    

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
                        <a href="dashboard-inventory">
                            <i class='bx bx-home-alt icon'></i>
                            <span class="text nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-link" id="inventory-link">
                        <a href="pages/inventory/inventory2">
                            <i class='bx bx-store-alt icon'></i>
                            <span class="text nav-text">Inventory</span>
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
            <h1 class="title">Dashboard</h1>
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
                    <span class="filter-label">Filter:</span>
                </div>
            

                <!-- Franchise Selection Buttons -->
                <div class="franchise-filter">
                    <div class="btn-group">
                        <button class="franchise-btn" data-franchise="Auntie Anne's">
                            <img src="assets/images/AuntieAnn.png" alt="Auntie Anne's Logo">
                            Auntie Anne's
                        </button>
                        <button class="franchise-btn" data-franchise="Macao Imperial Tea">
                            <img src="assets/images/MacaoImp.png" alt="Macao Imperial Tea Logo">
                            Macao Imperial
                        </button>
                        <button class="franchise-btn" data-franchise="Potato Corner">
                            <img src="assets/images/PotCor.png" alt="Potato Corner Logo">
                            Potato Corner
                        </button>
                    </div>
                </div>



                <!-- Branch Selection Buttons (Updated via AJAX) -->
                <div id="branch-buttons" class="mt-3"></div>


                <!-- Filters Section -->
                <div class="filter-section2">
                    <label for="startDate">Start Date:</label>
                    <input type="date" id="startDate" class="form-control">
                    
                    <label for="endDate">End Date:</label>
                    <input type="date" id="endDate" class="form-control">

                    <button class="btn btn-primary" onclick="generateReport()">Generate Report</button>
                </div>
            </div>

                <!-- KPI Cards -->
                <div class="row kpi-row">
                    <!-- Stock Level -->
                    <div class="col-md-4 kpi-col">
                        <div class="card kpi-card">
                            <div class="card-body">
                                <h2 class="kpi-number" id="stock-level">0</h2>
                                <h4 class="kpi-label">Stock Level</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Stockout Count -->
                    <div class="col-md-4 kpi-col">
                        <div class="card kpi-card">
                            <div class="card-body">
                                <h2 class="kpi-number" id="stockout-count">0</h2>
                                <h4 class="kpi-label">Stockout Count</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Total Wastage -->
                    <div class="col-md-4 kpi-col">
                        <div class="card kpi-card">
                            <div class="card-body">
                                <h2 class="kpi-number" id="total-wastage">0</h2>
                                <h4 class="kpi-label">Total Wastage</h4>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Charts Section -->
                <div class="charts-container">
                    <div class="chart-box">
                        <h2>High Turnover Chart</h2>
                        <canvas id="highTurnoverChart"></canvas>
                    </div>
                    <div class="chart-box">
                        <h2>Low Turnover Chart</h2>
                        <canvas id="lowTurnoverChart"></canvas>
                    </div>
                    <div class="chart-box">
                        <h2>Sell Through Chart</h2>
                        <canvas id="sellThroughChart"></canvas>
                    </div>
                </div>

                <!-- Report Modal -->
                <div id="reportModal" class="modal fade" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Inventory Report</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-secondary">
                                    <h6><strong>Applied Filters:</strong></h6>
                                    <p><strong>Franchisee(s):</strong> <span id="selectedFranchisees">All</span></p>
                                    <p><strong>Branch(es):</strong> <span id="selectedBranches">All</span></p>
                                    <p><strong>Date Range:</strong> <span id="selectedDateRange">Not Set</span></p>
                                </div>
                                <div class="table-responsive">
                                    <table id="reportTable" class="table table-bordered">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Item Name</th>
                                                <th>Stock Level</th>
                                                <th>Days Until Stockout</th>
                                                <th>Average Sales</th>
                                                <th class="text-end">Stock Waste</th>
                                            </tr>
                                        </thead>
                                        <tbody id="reportTableBody"></tbody>
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
    </section>

    



    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
        crossorigin="anonymous"></script>
    <script src="assets/js/inventory-kpi-dashboard-script.js"></script>
    <script src="assets/js/navbar.js"></script>

</body>
</html>
