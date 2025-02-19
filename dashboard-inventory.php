<?php
session_start();
include ("phpscripts/database-connection.php");
include ("phpscripts/check-login.php");

// Validate connection
if (!isset($con) || !$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get filters from GET request
$franchisees = isset($_GET['franchisees']) ? explode(",", $_GET['franchisees']) : [];
$branches = isset($_GET['branches']) ? explode(",", $_GET['branches']) : [];

// Build dynamic WHERE clause
$whereClauses = [];
if (!empty($franchisees)) {
    $franchiseeList = "'" . implode("','", array_map(fn($f) => mysqli_real_escape_string($con, $f), $franchisees)) . "'";
    $whereClauses[] = "franchisee IN ($franchiseeList)";
}
if (!empty($branches)) {
    $branchList = "'" . implode("','", array_map(fn($b) => mysqli_real_escape_string($con, $b), $branches)) . "'";
    $whereClauses[] = "branch IN ($branchList)";
}
$whereSQL = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

// Fetch stock data
$stockQuery = "SELECT branch, SUM(beginning - sold - waste) AS stock_available FROM item_inventory $whereSQL GROUP BY branch";
$stockResult = mysqli_query($con, $stockQuery);
$stockData = mysqli_fetch_all($stockResult, MYSQLI_ASSOC);

// Fetch stockouts
$stockoutQuery = "SELECT COUNT(*) AS stockout_count FROM item_inventory WHERE beginning - sold - waste = 0";
$stockoutResult = mysqli_query($con, $stockoutQuery);
$stockoutCount = ($stockoutResult) ? mysqli_fetch_assoc($stockoutResult)['stockout_count'] : 0;

// Fetch wastage trends
$wasteQuery = "SELECT franchisee, SUM(waste) AS total_waste FROM item_inventory GROUP BY franchisee";
$wasteResult = mysqli_query($con, $wasteQuery);
$wasteData = mysqli_fetch_all($wasteResult, MYSQLI_ASSOC);

// Fetch all franchisees
$franchiseeQuery = "SELECT DISTINCT franchisee FROM item_inventory";
$franchiseeResult = mysqli_query($con, $franchiseeQuery);
$franchisees = mysqli_fetch_all($franchiseeResult, MYSQLI_ASSOC);

// Fetch branches based on selected franchisees or return all if no filter is applied
$branches = [];

$branchQuery = "SELECT DISTINCT branch FROM item_inventory";
if (!empty($franchisees) && is_array($franchisees)) {
    $escapedFranchisees = [];

    foreach ($franchisees as $f) {
        if (is_string($f)) { // Ensure valid string
            $escapedFranchisees[] = "'" . mysqli_real_escape_string($con, $f) . "'";
        }
    }

    if (!empty($escapedFranchisees)) {
        $franchiseeList = implode(",", $escapedFranchisees);
        $branchQuery .= " WHERE franchisee IN ($franchiseeList)";
    }
}



$branchResult = mysqli_query($con, $branchQuery);

// ✅ Check if the query failed
if (!$branchResult) {
    die(json_encode(["error" => "SQL Error: " . mysqli_error($con)]));
}

// ✅ Fetch branches safely
while ($row = mysqli_fetch_assoc($branchResult)) {
    $branches[] = ["branch" => $row["branch"]];
}


// Fetch Days of Inventory for selected franchise and branch
$inventoryQuery = "
    SELECT i.item_name, 
           SUM(ii.beginning + ii.delivery - ii.waste - ii.sold) AS stock_available, 
           CASE 
               WHEN AVG(ii.sold) > 0 
               THEN SUM(ii.beginning + ii.delivery - ii.waste - ii.sold) / AVG(ii.sold) 
               ELSE NULL 
           END AS days_of_inventory
    FROM item_inventory ii
    JOIN items i ON ii.item_id = i.item_id
    $whereSQL
    GROUP BY ii.item_id";

    $inventoryResult = mysqli_query($con, $inventoryQuery);

    // ✅ Check if the query failed
    if (!$inventoryResult) {
        die(json_encode([
            "error" => "SQL Error: " . mysqli_error($con),
            "query" => $inventoryQuery
        ]));
    }
    
    // ✅ Fetch inventory data safely
    $inventoryData = [];
    while ($row = mysqli_fetch_assoc($inventoryResult)) {
        $inventoryData[] = [
            "item_name" => $row["item_name"],
            "days_of_inventory" => $row["days_of_inventory"] ?? "N/A"
        ];
    }
    

// Fetch top 5 items with high stock turnover
$highTurnoverQuery = "
    SELECT i.item_name, 
           CASE 
               WHEN SUM(ii.beginning + ii.delivery - ii.waste) > 0 
               THEN SUM(ii.sold) / SUM(ii.beginning + ii.delivery - ii.waste) 
               ELSE 0 
           END AS turnover_rate
    FROM item_inventory ii
    JOIN items i ON ii.item_id = i.item_id
    WHERE (ii.beginning + ii.delivery - ii.waste) > 0
    GROUP BY ii.item_id
    ORDER BY turnover_rate DESC
    LIMIT 5";
$highTurnoverResult = mysqli_query($con, $highTurnoverQuery);
$highTurnoverData = [];
while ($row = mysqli_fetch_assoc($highTurnoverResult)) {
    $highTurnoverData[] = $row;
}



// Fetch top 5 items with low stock turnover
$lowTurnoverQuery = "
    SELECT i.item_name, 
           CASE 
               WHEN SUM(ii.beginning + ii.delivery - ii.waste) > 0 
               THEN SUM(ii.sold) / SUM(ii.beginning + ii.delivery - ii.waste) 
               ELSE 0 
           END AS turnover_rate
    FROM item_inventory ii
    JOIN items i ON ii.item_id = i.item_id
    WHERE (ii.beginning + ii.delivery - ii.waste) > 0
    GROUP BY ii.item_id
    ORDER BY turnover_rate ASC
    LIMIT 5";
$lowTurnoverResult = mysqli_query($con, $lowTurnoverQuery);
$lowTurnoverData = [];
while ($row = mysqli_fetch_assoc($lowTurnoverResult)) {
    $lowTurnoverData[] = $row;
}


// Fix JSON output issue: Ensure JSON is only sent when requested
if (isset($_GET['json']) && $_GET['json'] == "true") {
    header("Content-Type: application/json");

    $response = [
        "inventoryData" => $inventoryData ?? [],
        "stockData" => $stockData ?? [],
        "stockoutCount" => $stockoutCount ?? 0,
        "wasteData" => $wasteData ?? [],
        "franchisees" => $franchisees ?? [],
        "branches" => $branches ?? [],
        "highTurnoverData" => $highTurnoverData ?? [],
        "lowTurnoverData" => $lowTurnoverData ?? []
    ];

    // Ensure JSON is properly formatted and log errors
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit();
}



mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/inventory-dashboard.css">
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
                <ul class="menu-links">
                    <li class="nav-link active">
                        <a href="dashboard-inventory">
                            <i class='bx bx-home-alt icon'></i>
                            <span class="text nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-link">
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
            <div class="container-header">
                <h1 class="title">Inventory Dashboard</h1>
            </div>
        </header>

        <div class="content" id="content-area">
            <div class="container">
                <div class="dash-content">
                    <div class="overview">
                        <div class="greeting">
                            <h2>Hi, <strong>Group/Branch Manager</strong>!</h2>
                        </div>
                        <h2 class="dashboard-title">Inventory Monitoring</h2>

                        <!-- Franchisee Selection -->
                        <div id="franchiseeButtons" class="filter-buttons">
                            <h4>Select Franchisee:</h4>
                        </div>

                        <!-- Branch Selection -->
                        <div id="branchButtons" class="filter-buttons" style="display: none;">
                            <h4>Select Branch:</h4>
                        </div>

                        <!-- KPI Cards -->
                        <div class="row kpi-row">
                            <div class="col-md-4 kpi-col">
                                <div class="card kpi-card">
                                    <div class="card-body">
                                        <h4>Current Stock Level</h4>
                                        <h2 class="kpi-number" id="stockLevel">0</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 kpi-col">
                                <div class="card kpi-card">
                                    <div class="card-body">
                                        <h4>Stockouts</h4>
                                        <h2 class="kpi-number" id="stockoutCount">0</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 kpi-col">
                                <div class="card kpi-card">
                                    <div class="card-body">
                                        <h4>Wastage Trends</h4>
                                        <h2 class="kpi-number" id="wasteTrends">0</h2>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- End KPI Cards -->

                        <div class="content" id="content-area">
                            <div class="container">
                                <h3>Top 5 High Stock Turnover</h3>
                                <canvas id="highTurnoverChart"></canvas>

                                <h3>Top 5 Low Stock Turnover</h3>
                                <canvas id="lowTurnoverChart"></canvas>
                            </div>
                        </div>

                        <div id="inventoryTableContainer">
                            <p>Select a franchise and branch to view inventory days.</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="assets/js/inventory-kpi-dashboard-script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>

</html>
