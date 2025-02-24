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
    header('Content-Type: application/json; charset=utf-8'); // ✅ Force JSON response

    if (!isset($con) || !$con) {
        echo json_encode(["error" => "Database connection failed: " . mysqli_connect_error()]);
        exit;
    }

    $franchise = $_POST['franchise'];

    $franchiseMap = [
        "Potato Corner" => "potato-corner",
        "Auntie Anne's" => "auntie-annes",
        "Macao Imperial Tea" => "macao-imperial"
    ];

    if (!isset($franchiseMap[$franchise])) {
        echo json_encode(["error" => "Invalid franchise name."]);
        exit;
    }
    $franchise = $franchiseMap[$franchise];

    $query = "SELECT DISTINCT branch FROM item_inventory WHERE franchisee = ?";
    $stmt = $con->prepare($query);
    if (!$stmt) {
        echo json_encode(["error" => "SQL Error: " . $con->error]);
        exit;
    }

    $stmt->bind_param("s", $franchise);
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
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['branch'])) {
    header('Content-Type: application/json; charset=utf-8'); // ✅ Ensure JSON response

    if (!isset($con) || !$con) {
        echo json_encode(["error" => "Database connection failed: " . mysqli_connect_error()]);
        exit;
    }

    $branch = $_POST['branch'];

    // ✅ Fetch inventory KPIs
    $query = "SELECT 
        SUM(beginning - sold - waste) AS stock_level,
        COUNT(CASE WHEN (beginning - sold - waste) = 0 THEN 1 END) AS stockout_count,
        SUM(waste) AS total_wastage
    FROM item_inventory WHERE branch = ?";

    $stmt = $con->prepare($query);
    if (!$stmt) {
        echo json_encode(["error" => "SQL Prepare Failed: " . $con->error]);
        exit;
    }

    $stmt->bind_param("s", $branch);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    
// ✅ Fetch top 5 high turnover items (Joining `item_inventory` with `item` table)
    $highTurnoverQuery = "SELECT i.item_name, (ii.sold / NULLIF(ii.beginning + ii.delivery - ii.waste, 0)) AS turnover_rate 
                          FROM item_inventory ii 
                          INNER JOIN items i ON ii.item_id = i.item_id 
                          WHERE ii.branch = ? 
                          ORDER BY turnover_rate DESC LIMIT 5";

    $lowTurnoverQuery = "SELECT i.item_name, (ii.sold / NULLIF(ii.beginning + ii.delivery - ii.waste, 0)) AS turnover_rate 
                         FROM item_inventory ii 
                         INNER JOIN items i ON ii.item_id = i.item_id 
                         WHERE ii.branch = ? 
                         ORDER BY turnover_rate ASC LIMIT 5";

    $stmtHigh = $con->prepare($highTurnoverQuery);
    $stmtHigh->bind_param("s", $branch);
    $stmtHigh->execute();
    $highTurnoverResult = $stmtHigh->get_result();

    $highTurnover = ["labels" => [], "values" => []];
    while ($row = $highTurnoverResult->fetch_assoc()) {
        $highTurnover["labels"][] = $row["item_name"];
        $highTurnover["values"][] = floatval($row["turnover_rate"]);
    }

    $stmtLow = $con->prepare($lowTurnoverQuery);
    $stmtLow->bind_param("s", $branch);
    $stmtLow->execute();
    $lowTurnoverResult = $stmtLow->get_result();

    $lowTurnover = ["labels" => [], "values" => []];
    while ($row = $lowTurnoverResult->fetch_assoc()) {
        $lowTurnover["labels"][] = $row["item_name"];
        $lowTurnover["values"][] = floatval($row["turnover_rate"]);
    }

    // sell through 
    // ✅ Fetch Sell-Through Rate Data with Date Filtering
    $startDate = $_POST["startDate"] ?? null;
    $endDate = $_POST["endDate"] ?? null;

    // ✅ Default date range (last 30 days) if no date is selected
    if (!$startDate) {
        $startDate = date("Y-m-d", strtotime("-30 days"));
    }
    if (!$endDate) {
        $endDate = date("Y-m-d");
    }

    $sellThroughQuery = "SELECT DATE(datetime_added) AS sale_date, 
                    (SUM(sold) / NULLIF(SUM(delivery), 0)) * 100 AS sell_through_rate
                    FROM item_inventory 
                    WHERE branch = ? 
                    AND DATE(datetime_added) BETWEEN ? AND ?
                    GROUP BY DATE(datetime_added)
                    ORDER BY sale_date ASC";

    $stmtSellThrough = $con->prepare($sellThroughQuery);
    $stmtSellThrough->bind_param("sss", $branch, $startDate, $endDate);
    $stmtSellThrough->execute();
    $sellThroughResult = $stmtSellThrough->get_result();

    $sellThroughRate = ["dates" => [], "values" => []];
    while ($row = $sellThroughResult->fetch_assoc()) {
        $sellThroughRate["dates"][] = $row["sale_date"];
        $sellThroughRate["values"][] = floatval($row["sell_through_rate"]);
    }


    // ✅ Final JSON Response
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
    


    <title>Inventory Analytics</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

                        
                

                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container mt-3">
    <h2>Inventory Analytics Dashboard</h2>

    <!-- Franchise Selection Buttons -->
    <div class="btn-group" role="group" aria-label="Select Franchise">
        <button class="btn btn-primary franchise-btn" data-franchise="Potato Corner">Potato Corner</button>
        <button class="btn btn-primary franchise-btn" data-franchise="Auntie Anne's">Auntie Anne's</button>
        <button class="btn btn-primary franchise-btn" data-franchise="Macao Imperial Tea">Macao Imperial Tea</button>
    </div>

    <!-- Branch Selection Buttons (Updated via AJAX) -->
    <div id="branch-buttons" class="mt-3"></div>

    <!-- filters -->
    
    <div class="row mt-4">
        <div class="col-md-6">
            <label>Start Date:</label>
            <input type="date" id="startDate" class="form-control">
        </div>
        <div class="col-md-6">
            <label>End Date:</label>
            <input type="date" id="endDate" class="form-control">
        </div>
    </div>
    <button class="btn btn-primary" onclick= "generateReport()">Generate Report</button>

    <!-- KPIs Section -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Stock Level</h5>
                    <p class="card-text" id="stock-level">-</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Stockout Count</h5>
                    <p class="card-text" id="stockout-count">-</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Wastage</h5>
                    <p class="card-text" id="total-wastage">-</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphs -->
    <h5>turnover chart</h5>
    <div class="chart-container">
    <div class="chart-box">
        <canvas id="highTurnoverChart"></canvas>
    </div>
    <div class="chart-box">
        <canvas id="lowTurnoverChart"></canvas>
    </div>
    </div>


    <!-- Sell-Through Rate Line Graph -->
    
    <div class="chart-container">
        <div class="chart-box">
            <canvas id="sellThroughChart"></canvas>
        </div>
    </div>

<!-- Report Modal -->
<div id="reportModal" class="modal fade" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title">Inventory Report</h5>
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

                                                

                                                <!-- Report Table -->
                                                <div class="table-responsive mt-3">
                                                    <table id="reportTable" class="table table-bordered table-hover table-striped">
                                                        <thead class="table-dark">
                                                            <tr>
                                                                <th>Item Name</th>
                                                                <th>Sell through rate</th>
                                                                <th>Days Until Stockout</th>
                                                                <th>average sales</th>
                                                                <th class="text-end">Stock waste</th>
                                                            </tr>
                                                        </thead>
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

<script>



$(document).ready(function() {
    let selectedBranch = "";

    // Fetch and display branches when a franchise is clicked
    $(".franchise-btn").click(function() {
        $(this).toggleClass("btn-selected btn-primary btn-outline-primary"); // Toggle selected class
        let franchise = $(this).data("franchise");
        $("#branch-buttons").empty();

        $.ajax({
    url: "dashboard-inventory.php", 
    type: "POST",
    data: { franchise: franchise },
    dataType: "json", // ✅ Ensures jQuery automatically parses JSON
    success: function(data) { // ✅ Use 'data' instead of 'response'
        console.log("Raw Response:", data); // ✅ Debugging
        try {
            let branches = data.branches; // ✅ Correctly extracts the branches array
            console.log("Parsed JSON:", branches);
            $("#branch-buttons").empty();
            branches.forEach(branch => {
                $("#branch-buttons").append(`<button class="btn btn-secondary branch-btn" data-branch="${branch}">${branch}</button>`);
            });
        } catch (error) {
            console.error("JSON Parsing Error:", error, "Received:", data);
        }
    },
    error: function(xhr, status, error) {
        console.error("AJAX Error:", xhr.responseText);
    }
});

    });

    // Fetch and update KPIs & Graphs when a branch is clicked
    $(document).on("click", ".branch-btn", function() {
        $(this).toggleClass("btn-selected btn-secondary btn-outline-secondary"); // Toggle selected class
        selectedBranch = $(this).data("branch");
        updateAnalytics(selectedBranch);
    });

    function updateAnalytics(branch) {
    let startDate = $("#startDate").val() || ""; // ✅ Ensure value is always defined
    let endDate = $("#endDate").val() || ""; // ✅ Ensure value is always defined

    $.ajax({
        url: "dashboard-inventory.php",
        type: "POST",
        data: {
            branch: branch,
            startDate: startDate, // ✅ Properly formatted key-value pairs
            endDate: endDate
        },
        dataType: "json",
        success: function(data) {
            console.log("Analytics Response:", data); // ✅ Debugging output

            if (data.error) {
                console.error("Error:", data.error);
                return;
            }

            // ✅ Update KPIs
            $("#stock-level").text(data.stock_level);
            $("#stockout-count").text(data.stockout_count);
            $("#total-wastage").text(data.total_wastage);

            // ✅ Update Graphs
            updateGraphs(data.high_turnover, data.low_turnover);
            updateSellThroughGraph(data.sell_through_rate);
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", xhr.responseText);
        }
    });
}

// ✅ Ensure date inputs trigger updates
$("#startDate, #endDate").change(function () {
    if (selectedBranch) {
        updateAnalytics(selectedBranch);
    }
});




let sellThroughChart; // ✅ Ensure global chart variable

function updateSellThroughGraph(sellThroughRate) {
    console.log("Updating Sell-Through Rate Graph...", sellThroughRate); // ✅ Debugging

    if (!sellThroughRate || !sellThroughRate.dates || sellThroughRate.dates.length === 0) {
        console.warn("No data available for Sell-Through Rate.");
        return;
    }

    if (sellThroughChart) sellThroughChart.destroy(); // ✅ Destroy previous chart

    const ctx = document.getElementById("sellThroughChart").getContext("2d");

    sellThroughChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: sellThroughRate.dates, // ✅ X-axis (time)
            datasets: [{
                label: "Sell-Through Rate (%)",
                data: sellThroughRate.values, // ✅ Y-axis (percentage)
                borderColor: "blue",
                backgroundColor: "rgba(0, 0, 255, 0.1)",
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // ✅ Ensures full width
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { return value + "%"; } // ✅ Show percentages
                    }
                }
            }
        }
    });

    console.log("Sell-Through Rate Graph Updated!");
}



let highTurnoverChart, lowTurnoverChart; // ✅ Ensure global chart variables

function updateGraphs(highTurnover, lowTurnover) {
    console.log("Updating Graphs...");
    console.log("High Turnover Data:", highTurnover);
    console.log("Low Turnover Data:", lowTurnover);

    if (!highTurnover.labels || !lowTurnover.labels || highTurnover.labels.length === 0 || lowTurnover.labels.length === 0) {
        console.warn("No data for graphs.");
        return;
    }

    if (highTurnoverChart) highTurnoverChart.destroy();
    if (lowTurnoverChart) lowTurnoverChart.destroy();

    const highTurnoverCanvas = document.getElementById("highTurnoverChart").getContext("2d");
    const lowTurnoverCanvas = document.getElementById("lowTurnoverChart").getContext("2d");

    highTurnoverChart = new Chart(highTurnoverCanvas, {
        type: "bar",
        data: {
            labels: highTurnover.labels,
            datasets: [{
                label: "High Turnover Rate",
                data: highTurnover.values,
                backgroundColor: "green"
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // ✅ Ensures the graph fills the container
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    lowTurnoverChart = new Chart(lowTurnoverCanvas, {
        type: "bar",
        data: {
            labels: lowTurnover.labels,
            datasets: [{
                label: "Low Turnover Rate",
                data: lowTurnover.values,
                backgroundColor: "red"
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // ✅ Ensures the graph fills the container
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    console.log("Graphs Updated Successfully!");
}



    // Date input change triggers new Sell-Through Rate calculation
    $("#startDate, #endDate").change(function() {
        if (selectedBranch) {
            updateAnalytics(selectedBranch);
        }
    });


});
const franchiseNameMap = {
    "Potato Corner": "Potato Corner",
    "Auntie Anne's": "Auntie Anne's",
    "Macao Imperial Tea": "Macao Imperial Tea"
};

    // GENERATE REPORT
    function generateReport() {
 // ✅ Get selected franchises
// ✅ Ensure selectedFranchisees and selectedBranches are always arrays
let selectedFranchisees = [...document.querySelectorAll(".franchise-btn.btn-selected")].map(btn => btn.dataset.franchise) || [];
let selectedBranches = [...document.querySelectorAll(".branch-btn.btn-selected")].map(btn => btn.dataset.branch) || [];


    // ✅ Ensure Franchise Names Are Mapped Correctly
    let franchiseeDisplay = selectedFranchisees.length > 0
        ? selectedFranchisees.map(f => franchiseNameMap[f] || f).join(", ")  // Map names if available
        : "All";  // Default if none selected

    // ✅ Update Modal Display
    document.getElementById("selectedFranchisees").innerText = franchiseeDisplay;
    document.getElementById("selectedBranches").innerText = selectedBranches.length > 0 
        ? selectedBranches.join(", ") 
        : "All";



    // ✅ Ensure startDate and endDate are always defined
let startDate = document.getElementById("startDate").value || "2000-01-01"; // Default to all-time
let endDate = document.getElementById("endDate").value || new Date().toISOString().split('T')[0]; // Default to today



    document.getElementById("selectedDateRange").innerText = (startDate && endDate) 
        ? `${startDate} to ${endDate}` 
        : "Not Set";

    // Show modal and fetch report
    $("#reportModal").modal("show");
    fetchReport("daily", selectedFranchisees, selectedBranches, startDate, endDate);
}

// fetch report
function fetchReport(reportType, selectedFranchisees, selectedBranches, startDate, endDate) {
    console.log("Fetching report:", reportType, selectedFranchisees, selectedBranches, startDate, endDate); // ✅ Debugging output

    $.ajax({
        url: "dashboard-inventory.php", // ✅ Fix: Add the correct URL
        type: "POST",
        data: {
            reportType: reportType,
            franchisees: selectedFranchisees,
            branches: selectedBranches,
            startDate: startDate,
            endDate: endDate
        },
        dataType: "json",
        success: function(response) {
    console.log("✅ Report Data Received:", response);
    
    // ✅ Log response details for debugging
    if (!response || typeof response !== "object") {
        console.error("❌ Invalid JSON response received:", response);
        return;
    }

    if (!response.data || !Array.isArray(response.data)) {
        console.warn("⚠ Response does not contain a valid 'data' array:", response);
        $("#reportTableBody").html("<tr><td colspan='5' class='text-center'>No valid data received.</td></tr>");
        return;
    }

            if (response.error) {
                console.error("⚠ Report Error:", response.error);
                $("#reportTableBody").html("<tr><td colspan='5' class='text-center text-danger'>Error fetching report data</td></tr>");
                return;
            }

            // ✅ Clear previous table data
            $("#reportTableBody").empty();

            if (!response.data || !Array.isArray(response.data) || response.data.length === 0) {
                console.warn("⚠ No data found or response is not an array:", response);
                $("#reportTableBody").html("<tr><td colspan='5' class='text-center'>No data available for selected filters.</td></tr>");
                return;
            }

            // ✅ Populate table with received data
            response.data.forEach(item => {
                $("#reportTableBody").append(`
                    <tr>
                        <td>${item.item_name}</td>
                        <td>${item.sell_through_rate ? item.sell_through_rate : "N/A"}</td>

                        <td>${item.days_until_stockout ? item.days_until_stockout.toFixed(1) : "N/A"}</td>
                        <td>${item.average_sales ? item.average_sales.toFixed(2) : "N/A"}</td>
                        <td class="text-end">${item.stock_waste ? item.stock_waste.toFixed(2) : "0.00"}</td>
                    </tr>
                `);
            });

        },
        error: function(xhr, status, error) {
            console.error("❌ AJAX Error:", xhr.responseText);
            $("#reportTableBody").html("<tr><td colspan='5' class='text-center text-danger'>Failed to fetch report data</td></tr>");
        }
    });
}

function exportTableToCSV() {
    let csv = [];
    let franchise = document.getElementById("selectedFranchisees").innerText;
    let branch = document.getElementById("selectedBranches").innerText;
    let dateRange = document.getElementById("selectedDateRange").innerText;

    // ✅ Add report title and metadata
    csv.push('"Sales Report"');
    csv.push(`"Franchise:","${franchise}"`);
    csv.push(`"Branch:","${branch}"`);
    csv.push(`"Date Range:","${dateRange}"`);
    csv.push(""); // Empty line before table

    let rows = document.querySelectorAll("#reportTable tr");

    // ✅ Loop through each row to extract the data
    for (let row of rows) {
        let cols = row.querySelectorAll("th, td");
        let rowData = [];

        for (let col of cols) {
            rowData.push('"' + col.innerText + '"'); // Wrap text in quotes to handle commas
        }

        csv.push(rowData.join(",")); // Join columns with commas
    }

    // ✅ Create a Blob (CSV File)
    let csvBlob = new Blob([csv.join("\n")], { type: "text/csv" });
    let csvUrl = URL.createObjectURL(csvBlob);

    // ✅ Create a Download Link
    let downloadLink = document.createElement("a");
    downloadLink.href = csvUrl;
    downloadLink.download = `Sales_Report_${new Date().toISOString().split("T")[0]}.csv`;
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}


function exportTableToPDF() {
    let { jsPDF } = window.jspdf;
    let doc = new jsPDF("p", "mm", "a4");

    // ✅ Fetch report details
    let franchise = document.getElementById("selectedFranchisees").innerText;
    let branch = document.getElementById("selectedBranches").innerText;
    let dateRange = document.getElementById("selectedDateRange").innerText;

    // ✅ Set Title and Metadata
    doc.setFontSize(14);
    doc.text("Sales Report", 10, 10);
    doc.setFontSize(10);
    doc.text(`Franchise: ${franchise}`, 10, 20);
    doc.text(`Branch: ${branch}`, 10, 25);
    doc.text(`Date Range: ${dateRange}`, 10, 30);

    let rows = [];
    let headers = [];

    document.querySelectorAll("#reportTable thead tr th").forEach(th => {
        headers.push(th.innerText);
    });

    document.querySelectorAll("#reportTable tbody tr").forEach(tr => {
        let rowData = [];
        tr.querySelectorAll("td").forEach(td => {
            rowData.push(td.innerText);
        });
        rows.push(rowData);
    });

    doc.autoTable({
        head: [headers],
        body: rows,
        startY: 35, // ✅ Move table below metadata
        theme: "grid"
    });

    doc.save(`Sales_Report_${new Date().toISOString().split("T")[0]}.pdf`);
}


</script>

</body>
</html>
