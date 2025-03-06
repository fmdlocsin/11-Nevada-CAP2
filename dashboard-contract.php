<?php

session_start();

include ("phpscripts/database-connection.php");
include ("phpscripts/check-login.php");

// Validate connection
if (!isset($con) || !$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Ensure user is logged in and set username
if (!isset($_SESSION['username']) && isset($_SESSION['user_email'])) {
    // Retrieve username from DB if session is missing it
    $query = "SELECT user_name FROM users_accounts WHERE user_email = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $_SESSION['user_email']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $_SESSION['username'] = $row['user_name'];
    }
}

// Debugging - Check if username is being stored
if (!isset($_SESSION['username'])) {
    echo "<script>console.log('❌ Username session is NOT set!');</script>";
} else {
    echo "<script>console.log('✅ Username session is set:', " . json_encode($_SESSION['username']) . ");</script>";
}

// Assign session username to variable for JavaScript
$username = $_SESSION['username'] ?? "Unknown User";
    echo "<script>var loggedInUser = " . json_encode($username) . ";</script>";



// Fetch Active Agreement Contracts (Dynamically calculated)
$activeAgreementContractsQuery = "
    SELECT COUNT(*) as total 
    FROM agreement_contract 
    WHERE agreement_date >= CURDATE()";
$activeAgreementContractsResult = mysqli_query($con, $activeAgreementContractsQuery);
$activeAgreementContracts = ($activeAgreementContractsResult) ? mysqli_fetch_assoc($activeAgreementContractsResult)['total'] : 0;


// Fetch Active Leasing Contracts
$activeLeasingContractsQuery = "SELECT COUNT(*) as total FROM lease_contract WHERE status = 'active'";
$activeLeasingContractsResult = mysqli_query($con, $activeLeasingContractsQuery);
$activeLeasingContracts = ($activeLeasingContractsResult) ? mysqli_fetch_assoc($activeLeasingContractsResult)['total'] : 0;

// Calculate Total Active Contracts
$totalActiveContracts = $activeAgreementContracts + $activeLeasingContracts;

// Fetch Agreement Contracts Expiring in the Next 30 Days
$expiringContractsQuery = "SELECT COUNT(*) as total 
                           FROM agreement_contract 
                           WHERE DATE(agreement_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                           AND LOWER(TRIM(status)) = 'active'";

$expiringContractsResult = mysqli_query($con, $expiringContractsQuery);
$expiringContracts = ($expiringContractsResult) ? mysqli_fetch_assoc($expiringContractsResult)['total'] : 0;


// Fetch Contracts Renewed in the Last 12 Months (Using agreement_date instead of renewal_date)
$renewedContractsQuery = "SELECT COUNT(*) as total FROM agreement_contract WHERE agreement_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
$renewedContractsResult = mysqli_query($con, $renewedContractsQuery);
$renewedContracts = ($renewedContractsResult) ? mysqli_fetch_assoc($renewedContractsResult)['total'] : 0;


// Fetch Expired Agreement Contracts (Dynamically Calculated)
$expiredContractsQuery = "
    SELECT COUNT(*) as total 
    FROM agreement_contract 
    WHERE agreement_date < CURDATE()";
$expiredContractsResult = mysqli_query($con, $expiredContractsQuery);
$expiredContracts = ($expiredContractsResult) ? mysqli_fetch_assoc($expiredContractsResult)['total'] : 0;

// // Fetch Total Contracts (Active + Expired)
// $totalContractsQuery = "
//     SELECT (SELECT COUNT(*) FROM agreement_contract) + 
//            (SELECT COUNT(*) FROM lease_contract) AS total";
// $totalContractsResult = mysqli_query($con, $totalContractsQuery);
// $totalContracts = ($totalContractsResult) ? mysqli_fetch_assoc($totalContractsResult)['total'] : 0;

// Fetch Total Agreement Contracts
$totalAgreementContractsQuery = "SELECT COUNT(*) as total FROM agreement_contract";
$totalAgreementContractsResult = mysqli_query($con, $totalAgreementContractsQuery);
$totalAgreementContracts = ($totalAgreementContractsResult) ? mysqli_fetch_assoc($totalAgreementContractsResult)['total'] : 0;

// Fetch Total Leasing Contracts
$totalLeasingContractsQuery = "SELECT COUNT(*) as total FROM lease_contract";
$totalLeasingContractsResult = mysqli_query($con, $totalLeasingContractsQuery);
$totalLeasingContracts = ($totalLeasingContractsResult) ? mysqli_fetch_assoc($totalLeasingContractsResult)['total'] : 0;

// Calculate Total Contracts
$totalContracts = $totalAgreementContracts + $totalLeasingContracts;



// Fetch Expired Contracts Trend (Grouped by Month)
$expiredContractsTrendQuery = "
    SELECT YEAR(agreement_date) AS year, MONTH(agreement_date) AS month, COUNT(*) AS expired_count
    FROM agreement_contract
    WHERE agreement_date < CURDATE()
    GROUP BY YEAR(agreement_date), MONTH(agreement_date)
    ORDER BY YEAR(agreement_date), MONTH(agreement_date)";

$expiredContractsTrendResult = mysqli_query($con, $expiredContractsTrendQuery);

$expiredContractsData = [];
while ($row = mysqli_fetch_assoc($expiredContractsTrendResult)) {
    $expiredContractsData[] = [
        "month" => "{$row['year']}-{$row['month']}",
        "count" => $row["expired_count"]
    ];
}

// Calculate Expiration Rate
$expirationRate = ($expiredContracts / $totalContracts) * 100;

// Determine expiration rate color class
$expirationRateClass = "low-risk"; // Default to green (good)

if ($expirationRate >= 50) {
    $expirationRateClass = "high-risk"; // Red (bad)
} elseif ($expirationRate >= 30) {
    $expirationRateClass = "medium-risk"; // Yellow (warning)
}



// Fetch Average Contract Duration (In Months)
$avgDurationQuery = "
    SELECT AVG(TIMESTAMPDIFF(MONTH, franchise_term, agreement_date)) AS avg_duration 
    FROM agreement_contract";
$avgDurationResult = mysqli_query($con, $avgDurationQuery);
$avgContractDuration = ($avgDurationResult) ? round(mysqli_fetch_assoc($avgDurationResult)['avg_duration'], 2) : 0;

// Fetch Contract Duration Per Franchise
$durationPerFranchiseQuery = "
    SELECT franchisee, AVG(TIMESTAMPDIFF(MONTH, franchise_term, agreement_date)) AS avg_duration
    FROM agreement_contract
    GROUP BY franchisee";
$durationPerFranchiseResult = mysqli_query($con, $durationPerFranchiseQuery);

$durationPerFranchiseData = [];
while ($row = mysqli_fetch_assoc($durationPerFranchiseResult)) {
    $durationPerFranchiseData[] = [
        "franchise" => $row["franchisee"],
        "duration" => round($row["avg_duration"], 2)
    ];
}

// Fetch Contract Duration Trend (Grouped by Month)
$contractDurationTrendQuery = "
    SELECT YEAR(franchise_term) AS year, MONTH(franchise_term) AS month, 
           AVG(TIMESTAMPDIFF(MONTH, franchise_term, agreement_date)) AS avg_duration
    FROM agreement_contract
    GROUP BY YEAR(franchise_term), MONTH(franchise_term)
    ORDER BY YEAR(franchise_term), MONTH(franchise_term)";
$contractDurationTrendResult = mysqli_query($con, $contractDurationTrendQuery);

$contractDurationTrendData = [];
while ($row = mysqli_fetch_assoc($contractDurationTrendResult)) {
    $contractDurationTrendData[] = [
        "month" => "{$row['year']}-{$row['month']}",
        "duration" => round($row["avg_duration"], 2)
    ];
}

// Fetch Franchise Agreement Data
$franchiseQuery = "
    SELECT franchisee, 
        COUNT(*) AS total_contracts,  -- Total contracts (active + expired)
        COUNT(CASE WHEN LOWER(TRIM(status)) = 'active' AND agreement_date >= CURDATE() THEN 1 END) AS active_contracts,
        COUNT(CASE WHEN agreement_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 END) AS expiring_contracts,  -- Fix: Include expiring contracts
        COUNT(CASE WHEN agreement_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) THEN 1 END) AS renewed_contracts,  -- Fix: Include renewed contracts
        COUNT(CASE WHEN agreement_date < CURDATE() THEN 1 END) AS expired_contracts
    FROM agreement_contract
    GROUP BY franchisee";

$franchiseResult = mysqli_query($con, $franchiseQuery);


$franchiseNames = [];
$totalContractsPerFranchise = []; // New array for total contracts per franchise

while ($row = mysqli_fetch_assoc($franchiseResult)) {
    $franchiseNames[] = ucfirst(str_replace("-", " ", $row['franchisee']));
    $totalContractsPerFranchise[] = $row['total_contracts']; // Store total contracts
}


// Fetch Leasing Contracts Breakdown Per Franchise
$leasingContractsQuery = "
    SELECT franchisee, 
        COUNT(*) AS total_leases,  -- Total leases (active + expired)
        COUNT(CASE WHEN status = 'active' THEN 1 END) AS active_leases,
        COUNT(CASE WHEN end_date < CURDATE() THEN 1 END) AS expired_leases
    FROM lease_contract
    GROUP BY franchisee";

$leasingResult = mysqli_query($con, $leasingContractsQuery);

$leasingFranchiseNames = [];
$totalLeasesPerFranchise = []; // New array for total leases per franchise

while ($row = mysqli_fetch_assoc($leasingResult)) {
    $leasingFranchiseNames[] = ucfirst(str_replace("-", " ", $row['franchisee']));
    $totalLeasesPerFranchise[] = $row['total_leases']; // Store total leases
}


// Convert PHP data to JavaScript variables
echo "<script> 
        var franchiseNames = " . json_encode($franchiseNames) . ";
        var totalContractsPerFranchise = " . json_encode($totalContractsPerFranchise) . "; 

        var leasingFranchiseNames = " . json_encode($leasingFranchiseNames) . ";
        var totalLeasesPerFranchise = " . json_encode($totalLeasesPerFranchise) . ";
      </script>";



// Close the database connection
// mysqli_close($con);

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contracts Dashboard</title>
    <!-- ========= CSS ========= -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard2.css">
    <link rel="stylesheet" href="assets/css/contract-dashboard.css">
    <!-- ===== Boxicons CSS ===== -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- Send Expired Contracts Data to JavaScript -->
    <script>
        var expiredContractsData = <?php echo json_encode($expiredContractsData); ?>;
    </script>

    <script>
        var contractDurationTrendData = <?php echo json_encode($contractDurationTrendData); ?>;
        var durationPerFranchiseData = <?php echo json_encode($durationPerFranchiseData); ?>;
    </script>

    <!-- Send Franchise Data to JavaScript -->
    <script>
        var franchiseNames = <?php echo json_encode($franchiseNames); ?>;
        var activeContracts = <?php echo json_encode($activeContracts); ?>;
    </script>

    <script>
        var leasingFranchiseNames = <?php echo json_encode($leasingFranchiseNames); ?>;
        var activeLeases = <?php echo json_encode($activeLeases); ?>;
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
                        <a href="dashboard-contract">
                            <i class='bx bx-home-alt icon'></i>
                            <span class="text nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-link" id="franchising-link">
                        <a href="pages/contract/franchiseeAgreement">
                            <i class='bx bx-file icon'></i>
                            <span class="text nav-text">Franchising Agreement</span>
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
                            <h2>Hi, <strong>Business Development Officer</strong>!</h2>
                        </div>
                        <div class="title">
                        <i class='bx bx-time-five'></i>
                        <span class="text">Analytics</span>
                        </div>
                        <div class="container">
                        
                            <!-- <h2 class="dashboard-title">Franchise Agreement Monitoring</h2> -->

                <!-- ----------------------------------- KPI CARDS PART ----------------------------------- -->

                            <div class="row kpi-row">

                                <div class="col-md-4 kpi-col">
                                    <div class="card kpi-card total-contracts">
                                        <div class="card-body">
                                            <i class="kpi-icon ni ni-collection"></i> <!-- Contracts Icon -->
                                            <h4>Total Contracts</h4>
                                            <h2 class="kpi-number"><?php echo $totalContracts; ?></h2>
                                            <p class="kpi-subtext">Agreement: <strong><?php echo $totalAgreementContracts; ?></strong> | Leasing: <strong><?php echo $totalLeasingContracts; ?></strong></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 kpi-col">
                                    <div class="card kpi-card active-contracts">
                                        <div class="card-body">
                                            <h4>Active Contracts</h4>
                                            <h2 class="kpi-number"><?php echo $totalActiveContracts; ?></h2>
                                            <p class="kpi-subtext">Agreement: <strong><?php echo $activeAgreementContracts; ?></strong> | Leasing: <strong><?php echo $activeLeasingContracts; ?></strong></p>
                                        </div>
                                    </div>
                                </div>


                                <div class="col-md-4 kpi-col">
                                    <div class="card kpi-card">
                                        <div class="card-body">
                                            <i class="kpi-icon ni ni-time-alarm"></i> <!-- Icon -->
                                            <h4>Contracts Expiring Next Month</h4>
                                            <h2 class="kpi-number" id="expiringContracts"><?php echo $expiringContracts; ?></h2>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 kpi-col">
                                    <div class="card kpi-card expired">
                                        <div class="card-body">
                                            <i class="kpi-icon ni ni-alert-circle-exc"></i> <!-- Warning Icon -->
                                            <h4>Expired Contracts</h4>
                                            <h2 class="kpi-number" id="expiredContracts"><?php echo $expiredContracts; ?></h2>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 kpi-col">
                                    <div class="card kpi-card expiration <?php echo $expirationRateClass; ?>">
                                        <div class="card-body">
                                            <i class="kpi-icon ni ni-chart-pie-35"></i>
                                            <h4>Expiration Rate</h4>
                                            <h2 class="kpi-number"><?php echo round($expirationRate, 2); ?>%</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>

                <!-- ----------------------------------- TABLE PART ----------------------------------- -->
                        <div class="section-header2">
                            <div class="d-inline-flex align-items-center gap-3">
                                <h3 class="table-title mb-0">Franchisee Agreement Contracts Breakdown</h3>
                                <button id="generateFranchiseReport" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#franchiseReportModal">
                                    Generate Report
                                </button>
                            </div>
                        </div>



                            <!-- Flexbox Row: Pie Chart on the Left, Table on the Right -->
                            <div class="row align-items-center">
                                <!-- Pie Chart Column -->
                                <div class="col-md-6 d-flex justify-content-center">
                                    <div class="chart-container pie-chart-container">
                                        <h5 class="text-center">Total Agreement Contracts Distribution</h5>
                                        <canvas id="activeContractsChart"></canvas>
                                    </div>
                                </div>

                                <!-- Table Column -->
                                <div class="col-md-6">
                                    <table class="content-table">
                                        <thead>
                                            <tr>
                                                <th>Franchisee Name</th>
                                                <th>Active Contracts</th>
                                                <th>Expiring Next Month</th>
                                                <th>Expired Contracts</th>
                                                <th>Renewal Rate (%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $franchiseResult = mysqli_query($con, $franchiseQuery);
                                            while ($row = mysqli_fetch_assoc($franchiseResult)) {
                                                
                                                // Define the name mapping
                                                $franchiseNameMap = [
                                                    "auntie anne" => "Auntie Anne's",
                                                    "macao imperial" => "Macao Imperial",
                                                    "potato corner" => "Potato Corner"
                                                ];
                                                
                                                $franchiseResult = mysqli_query($con, $franchiseQuery);
                                                while ($row = mysqli_fetch_assoc($franchiseResult)) {
                                                    // Convert DB name: replace hyphens with spaces, lowercase it, and trim spaces
                                                    $rawFranchiseName = strtolower(str_replace("-", " ", trim($row['franchisee'])));
                                                
                                                    // Apply name mapping or use the default formatted name
                                                    $franchiseName = isset($franchiseNameMap[$rawFranchiseName]) ? 
                                                        $franchiseNameMap[$rawFranchiseName] : ucfirst($rawFranchiseName);
                                                
                                                    // Retrieve contract values
                                                    $activeContracts = isset($row['active_contracts']) ? $row['active_contracts'] : 0;
                                                    $expiredContracts = isset($row['expired_contracts']) ? $row['expired_contracts'] : 0;
                                                
                                                    // Renewal rate calculation
                                                    $renewalRate = ($activeContracts + $expiredContracts) > 0 
                                                        ? ($activeContracts / ($activeContracts + $expiredContracts)) * 100 
                                                        : 0;
                                                
                                                    // Output the row in the table
                                                    echo "<tr>
                                                            <td>{$franchiseName}</td>
                                                            <td>{$row['active_contracts']}</td>
                                                            <td>{$row['expiring_contracts']}</td>
                                                            <td>{$row['expired_contracts']}</td>
                                                            <td>" . round($renewalRate, 2) . "%</td>
                                                          </tr>";
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>



                            <div class="d-inline-flex align-items-center gap-3">
                                <h3 class="table-title mb-0">Leasing Agreement Contracts Breakdown</h3>
                                <button id="generateLeasingReport" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#leasingReportModal">
                                    Generate Report
                                </button>
                            </div>



                            <!-- Flexbox Row: Pie Chart on the Left, Table on the Right -->
                            <div class="row align-items-center">
                                <!-- Leasing Pie Chart Column -->
                                <div class="col-md-6 d-flex justify-content-center">
                                    <div class="chart-container pie-chart-container">
                                        <h5 class="text-center">Total Leasing Contracts Distribution</h5>
                                        <canvas id="leasingContractsChart"></canvas>
                                    </div>
                                </div>

                                <!-- Leasing Contracts Table Column -->
                                <div class="col-md-6">
                                    <table class="content-table">
                                        <thead>
                                            <tr>
                                                <th>Franchisee Name</th>
                                                <th>Active Leases</th>
                                                <th>Expiring Next Month</th>
                                                <th>Expired Leases</th>
                                                <th>Occupancy Rate (%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Fetch Leasing Contracts Breakdown Per Franchise
                                            $leasingContractsQuery = "
                                                SELECT franchisee, 
                                                    COUNT(CASE WHEN status = 'active' THEN 1 END) AS active_leases,
                                                    COUNT(CASE WHEN end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH) THEN 1 END) AS expiring_leases,
                                                    COUNT(CASE WHEN end_date < CURDATE() THEN 1 END) AS expired_leases
                                                FROM lease_contract
                                                GROUP BY franchisee";

                                            $leasingResult = mysqli_query($con, $leasingContractsQuery);

                                            // Check if query execution failed
                                            if (!$leasingResult) {
                                                die("Error in leasing contracts query: " . mysqli_error($con));
                                            }

                                            while ($row = mysqli_fetch_assoc($leasingResult)) {
                                                // Convert DB name: replace hyphens with spaces, lowercase it, and trim spaces
                                                $rawFranchiseName = strtolower(str_replace("-", " ", trim($row['franchisee'])));
                                            
                                                // Apply name mapping or use default formatted name
                                                $formattedFranchiseName = isset($franchiseNameMap[$rawFranchiseName]) ? 
                                                    $franchiseNameMap[$rawFranchiseName] : ucfirst($rawFranchiseName);
                                            
                                                // Ensure no missing values in leasing data
                                                $activeLeases = isset($row['active_leases']) ? $row['active_leases'] : 0;
                                                $expiringLeases = isset($row['expiring_leases']) ? $row['expiring_leases'] : 0;
                                                $expiredLeases = isset($row['expired_leases']) ? $row['expired_leases'] : 0;
                                            
                                                // Calculate occupancy rate (avoid division by zero)
                                                $occupancyRate = ($activeLeases / max(1, ($activeLeases + $expiredLeases))) * 100;
                                            
                                                // Output formatted table row
                                                echo "<tr>
                                                        <td>{$formattedFranchiseName}</td>
                                                        <td>{$activeLeases}</td>
                                                        <td>{$expiringLeases}</td>
                                                        <td>{$expiredLeases}</td>
                                                        <td>" . round($occupancyRate, 2) . "%</td>
                                                      </tr>";
                                            }
                                            
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

    

                <!-- ----------------------------------- CONTRACT DURATION PART ----------------------------------- -->
                            <!-- KPI Cards -->
                                <div class="row kpi-row">
                                    <div class="col-md-12">
                                        <div class="card kpi-card avg-duration-card">
                                            <div class="card-body">
                                                <h4>Average Contract Duration</h4>
                                                <h2 class="kpi-number"><?php echo $avgContractDuration; ?> Months</h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Graphs Row -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="chart-container small-chart">
                                            <h5>Contract Duration Over Time</h5>
                                            <canvas id="contractDurationTrendChart"></canvas>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="chart-container small-chart">
                                            <h5>Contract Duration Per Franchise</h5>
                                            <canvas id="contractDurationPerFranchiseChart"></canvas>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="chart-container small-chart">
                                            <h5>Expired Contracts Over Time</h5>
                                            <canvas id="contractRenewalChart"></canvas>
                                        </div>
                                    </div>
                                </div>

                <!-- ----------------------------------- REPORT MODAL PART ----------------------------------- -->

                               <!-- Franchise Agreement Report Modal -->
                                <div class="modal fade" id="franchiseReportModal" tabindex="-1" aria-labelledby="franchiseReportLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-file-contract"></i> Franchisee Agreement Contracts Report
                                                    <span class="badge bg-success ms-2">Agreement</span>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body">
                                                <!-- Franchisee Filter -->
                                                <div class="mb-3">
                                                    <label for="franchiseeFilter" class="form-label"><strong>Filter by Franchisee:</strong></label>
                                                    
                                                    <!-- Franchise Agreement Filter -->
                                                    <select id="franchiseeFilter" class="form-select">
                                                        <option value="">All Franchisees</option>
                                                        <?php
                                                        // Franchise name mapping
                                                        $franchiseNameMap = [
                                                            "auntie-anne" => "Auntie Anne's",
                                                            "macao-imperial" => "Macao Imperial",
                                                            "potato-corner" => "Potato Corner"
                                                        ];

                                                        // Fetch distinct franchisee names
                                                        $franchiseeQuery = "SELECT DISTINCT franchisee FROM agreement_contract";
                                                        $franchiseeResult = mysqli_query($con, $franchiseeQuery);
                                                        
                                                        while ($row = mysqli_fetch_assoc($franchiseeResult)) {
                                                            $rawFranchisee = strtolower(trim($row['franchisee']));
                                                            $formattedFranchisee = isset($franchiseNameMap[$rawFranchisee]) ? $franchiseNameMap[$rawFranchisee] : ucfirst(str_replace("-", " ", $rawFranchisee));
                                                            
                                                            echo "<option value='{$row['franchisee']}'>{$formattedFranchisee}</option>";
                                                        }
                                                        ?>
                                                    </select>

                                                </div>

                                                <!-- Summary Section -->
                                                <div id="franchiseSummary" class="franchise-summary"></div>

                                                <!-- Report Tables for Each Franchisee -->
                                                <div id="franchiseReportContent"></div>
                                            </div>

                                            <div class="modal-footer">
                                                <button class="btn btn-primary" id="exportFranchiseCSV">
                                                    <i class="fas fa-file-csv"></i> Export Agreement Data (CSV)
                                                </button>
                                                <button class="btn btn-danger" id="exportFranchisePDF">
                                                    <i class="fas fa-file-pdf"></i> Download Agreement Report (PDF)
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                                <!-- Leasing Contracts Report Modal -->
                                <div class="modal fade" id="leasingReportModal" tabindex="-1" aria-labelledby="leasingReportLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-building"></i> Leasing Contracts Report
                                                    <span class="badge bg-info ms-2">Leasing</span>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body">
                                                <!-- Franchisee Filter -->
                                                <div class="mb-3">
                                                    <label for="leasingFranchiseeFilter" class="form-label"><strong>Filter by Franchisee:</strong></label>
                                                    
                                                    
                                                    <!-- Leasing Agreement Filter -->
                                                    <select id="leasingFranchiseeFilter" class="form-select">
                                                        <option value="">All Franchisees</option>
                                                        <?php
                                                        // Fetch distinct franchisee names from lease_contract table
                                                        $leasingFranchiseeQuery = "SELECT DISTINCT franchisee FROM lease_contract";
                                                        $leasingFranchiseeResult = mysqli_query($con, $leasingFranchiseeQuery);

                                                        while ($row = mysqli_fetch_assoc($leasingFranchiseeResult)) {
                                                            $rawFranchisee = strtolower(trim($row['franchisee']));
                                                            $formattedFranchisee = isset($franchiseNameMap[$rawFranchisee]) ? $franchiseNameMap[$rawFranchisee] : ucfirst(str_replace("-", " ", $rawFranchisee));

                                                            echo "<option value='{$row['franchisee']}'>{$formattedFranchisee}</option>";
                                                        }
                                                        ?>
                                                    </select>

                                                </div>

                                                <!-- Summary Section -->
                                                <div id="leasingSummary" class="leasing-summary"></div>

                                                <!-- Report Tables for Each Leasing Franchisor -->
                                                <div id="leasingReportContent"></div>
                                            </div>

                                            <div class="modal-footer">
                                                <button class="btn btn-primary" id="exportLeasingCSV">
                                                    <i class="fas fa-file-csv"></i> Export Leasing Data (CSV)
                                                </button>
                                                <button class="btn btn-danger" id="exportLeasingPDF">
                                                    <i class="fas fa-file-pdf"></i> Download Leasing Report (PDF)
                                                </button>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/dashboard-contract-script.js"></script>
    <script src="assets/js/navbar.js"></script>
    <!-- <script src="assets/js/content.js"></script> -->

    <!-- Close the connection at the very end -->
    <?php mysqli_close($con); ?>

    <!-- jsPDF (for PDF export) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- jsPDF AutoTable Plugin (for table formatting in PDF) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>


</body>

</html>