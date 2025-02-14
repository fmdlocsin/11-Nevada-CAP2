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

$franchiseeList = "";
if (!empty($franchisees)) {
    $franchiseeList = "'" . implode("','", array_map(fn($f) => mysqli_real_escape_string($con, $f), $franchisees)) . "'";
    $whereClauses[] = "franchisee IN ($franchiseeList)";
}

$branchList = "";
if (!empty($branches)) {
    $branchList = "'" . implode("','", array_map(fn($b) => mysqli_real_escape_string($con, $b), $branches)) . "'";
    $whereClauses[] = "branch IN ($branchList)";
}

$whereSQL = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

// Fetch stock data
$stockQuery = "SELECT branch, SUM(beginning - sold - waste) AS stock_available FROM item_inventory $whereSQL GROUP BY branch";
$stockResult = mysqli_query($con, $stockQuery);
$stockData = mysqli_fetch_all($stockResult, MYSQLI_ASSOC);

// Fetch restocking frequency
$restockQuery = "SELECT COUNT(*) AS restock_count FROM item_inventory WHERE delivery > 0";
$restockResult = mysqli_query($con, $restockQuery);
$restockCount = ($restockResult) ? mysqli_fetch_assoc($restockResult)['restock_count'] : 0;

// Fetch stockouts
$stockoutQuery = "SELECT COUNT(*) AS stockout_count FROM item_inventory WHERE beginning - sold - waste = 0";
$stockoutResult = mysqli_query($con, $stockoutQuery);
$stockoutCount = ($stockoutResult) ? mysqli_fetch_assoc($stockoutResult)['stockout_count'] : 0;

// Fetch inventory turnover rate
$turnoverQuery = "SELECT branch, (SUM(sold) / NULLIF(SUM(beginning) + SUM(delivery), 0)) * 100 AS turnover_rate FROM item_inventory GROUP BY branch";
$turnoverResult = mysqli_query($con, $turnoverQuery);
$turnoverData = mysqli_fetch_all($turnoverResult, MYSQLI_ASSOC);

// Fetch wastage trends
$wasteQuery = "SELECT franchisee, SUM(waste) AS total_waste FROM item_inventory GROUP BY franchisee";
$wasteResult = mysqli_query($con, $wasteQuery);
$wasteData = mysqli_fetch_all($wasteResult, MYSQLI_ASSOC);

// Fetch all franchisees
$franchiseeQuery = "SELECT DISTINCT franchisee FROM item_inventory";
$franchiseeResult = mysqli_query($con, $franchiseeQuery);
$franchisees = mysqli_fetch_all($franchiseeResult, MYSQLI_ASSOC);

// Fetch branches only if franchisees are selected
$branches = [];
if (!empty($franchiseeList)) {
    $branchQuery = "SELECT DISTINCT branch FROM item_inventory WHERE franchisee IN ($franchiseeList)";
    $branchResult = mysqli_query($con, $branchQuery);
    $branches = mysqli_fetch_all($branchResult, MYSQLI_ASSOC);
}

// Fix JSON output issue: Ensure JSON is only sent when requested
if (isset($_GET['json']) && $_GET['json'] == "true") {
    header("Content-Type: application/json");
    echo json_encode([
        "stockData" => $stockData,
        "restockCount" => $restockCount,
        "stockoutCount" => $stockoutCount,
        "turnoverData" => $turnoverData,
        "wasteData" => $wasteData,
        "franchisees" => $franchisees,
        "branches" => $branches
    ]);
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
    <!-- ========= CSS ========= -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/inventory-dashboard.css">
    <!-- ===== Boxicons CSS ===== -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- Send KPI Data to JavaScript -->
    <script>
        var stockData = <?php echo $stockDataJSON; ?>;
        var turnoverData = <?php echo $turnoverDataJSON; ?>;
        var wasteData = <?php echo $wasteDataJSON; ?>;
    </script>


    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Function to create buttons dynamically
            function createButtons(containerId, data, type) {
                let container = document.getElementById(containerId);
                container.innerHTML = ""; // Clear previous buttons
                
                data.forEach(item => {
                    let btn = document.createElement("button");
                    btn.classList.add("btn", "btn-primary", "m-2");
                    btn.innerText = item[type];  // Use 'franchisee' or 'branch' as key
                    btn.setAttribute("data-name", item[type]);

                    // Add click event
                    btn.addEventListener("click", function () {
                        this.classList.toggle("btn-success"); // Toggle selection
                    });

                    container.appendChild(btn);
                });
            }

            // Fetch JSON data from PHP
            fetch("dashboard-inventory.php?json=true")
                .then(response => response.json())
                .then(data => {
                    // ✅ Populate Franchisee Buttons
                    createButtons("franchiseeButtons", data.franchisees, "franchisee");

                    // ✅ Populate Branch Buttons only when a franchisee is selected
                    document.getElementById("franchiseeButtons").addEventListener("click", function (e) {
                        if (e.target.tagName === "BUTTON") {
                            let selectedFranchisee = e.target.getAttribute("data-name");
                            let filteredBranches = data.branches.filter(branch => branch.franchisee === selectedFranchisee);

                            createButtons("branchButtons", filteredBranches, "branch");
                            document.getElementById("branchButtons").style.display = "block"; // Show branch buttons
                        }
                    });

                    // ✅ Update stock data
                    document.getElementById("stockLevel").innerText = data.stockData.reduce((acc, item) => acc + Number(item.stock_available || 0), 0);
                    
                    // ✅ Update Turnover Rate
                    document.getElementById("turnoverRate").innerText = data.turnoverData.map(item => 
                        `${item.branch}: ${parseFloat(item.turnover_rate).toFixed(2)}%`
                    ).join(', ');

                    // ✅ Update Wastage Trends
                    document.getElementById("wasteTrends").innerText = data.wasteData.map(item => 
                        `${item.franchisee}: ${item.total_waste}`
                    ).join(', ');
                })
                .catch(error => console.error("Error loading data:", error));
        });
    </script>



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
                        <div class="container">
                            <h2 class="dashboard-title">Inventory Monitoring</h2>

                             <!-- Franchisee Selection Buttons -->
                            <div id="franchiseeButtons" class="filter-buttons">
                                <h4>Select Franchisee:</h4>
                            </div>

                            <!-- Branch Selection Buttons (Disabled until franchisee selected) -->
                            <div id="branchButtons" class="filter-buttons" style="display: none;">
                                <h4>Select Branch:</h4>
                            </div>

                            
                            <!-- KPI CARDS -->
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
                                            <h4>Restocking Frequency</h4>
                                            <h2 class="kpi-number"><?php echo $restockCount; ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 kpi-col">
                                    <div class="card kpi-card">
                                        <div class="card-body">
                                            <h4>Stockouts</h4>
                                            <h2 class="kpi-number"><?php echo $stockoutCount; ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 kpi-col">
                                    <div class="card kpi-card">
                                        <div class="card-body">
                                            <h4>Turnover Rate</h4>
                                            <h2 class="kpi-number" id="turnoverRate">0%</h2>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- JS -->
   <!-- JS -->
   <script>
        document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("stockLevel").innerText = stockData.reduce((acc, item) => acc + Number(item.stock_available || 0), 0);
        
        document.getElementById("turnoverRate").innerText = turnoverData.map(item => 
            `${item.branch}: ${parseFloat(item.turnover_rate).toFixed(2)}%`
        ).join(', ');

        document.getElementById("wasteTrends").innerText = wasteData.map(item => 
            `${item.franchisee}: ${item.total_waste}`
        ).join(', ');
    });

    </script>

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
    <!-- <script src="assets/js/content.js"></script> -->
</body>

</html>