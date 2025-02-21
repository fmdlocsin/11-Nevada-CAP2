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

    <title>Inventory Analytics</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    <div class="chart-container">
        <div class="chart-box">
            <canvas id="sellThroughChart"></canvas>
        </div>
    </div>

</div>

<script>
$(document).ready(function() {
    let selectedBranch = "";

    // Fetch and display branches when a franchise is clicked
    $(".franchise-btn").click(function() {
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
</script>

</body>
</html>
