<?php

session_start();

include ("phpscripts/database-connection.php");
include ("phpscripts/check-login.php");

// Validate connection
if (!isset($con) || !$con) {
    die("Database connection failed: " . mysqli_connect_error());
}


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

// Fetch Total Contracts
$totalContractsQuery = "SELECT COUNT(*) as total FROM agreement_contract";
$totalContractsResult = mysqli_query($con, $totalContractsQuery);
$totalContracts = ($totalContractsResult) ? mysqli_fetch_assoc($totalContractsResult)['total'] : 1;

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
        COUNT(CASE WHEN status = 'active' THEN 1 END) AS active_contracts,
        COUNT(CASE WHEN agreement_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH) THEN 1 END) AS expiring_contracts,
        COUNT(CASE WHEN agreement_date < CURDATE() THEN 1 END) AS expired_contracts,
        COUNT(CASE WHEN agreement_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) THEN 1 END) AS renewed_contracts
    FROM agreement_contract
    GROUP BY franchisee";

$franchiseResult = mysqli_query($con, $franchiseQuery);

// Arrays to store franchise data for JavaScript
$franchiseNames = [];
$activeContracts = [];

while ($row = mysqli_fetch_assoc($franchiseResult)) {
    $franchiseNames[] = ucfirst(str_replace("-", " ", $row['franchisee'])); // Format name
    $activeContracts[] = $row['active_contracts'];
}

// Fetch Leasing Contracts Breakdown Per Franchise
$leasingContractsQuery = "
    SELECT franchisee, 
        COUNT(CASE WHEN status = 'active' THEN 1 END) AS active_leases,
        COUNT(CASE WHEN end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH) THEN 1 END) AS expiring_leases,
        COUNT(CASE WHEN end_date < CURDATE() THEN 1 END) AS expired_leases
    FROM lease_contract
    GROUP BY franchisee";

$leasingResult = mysqli_query($con, $leasingContractsQuery);

// Prepare data for JavaScript
$leasingFranchiseNames = [];
$activeLeases = [];

while ($row = mysqli_fetch_assoc($leasingResult)) {
    $leasingFranchiseNames[] = ucfirst(str_replace("-", " ", $row['franchisee']));
    $activeLeases[] = $row['active_leases'];
}

// Convert PHP data to JavaScript variables
echo "<script> 
        var franchiseNames = " . json_encode($franchiseNames) . ";
        var activeContracts = " . json_encode($activeContracts) . ";
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
    <title>Dashboard</title>
    <!-- ========= CSS ========= -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
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
                            <h3 class="table-title">Franchise Agreement Contracts Breakdown</h3>

                            <!-- Flexbox Row: Pie Chart on the Left, Table on the Right -->
                            <div class="row align-items-center">
                                <!-- Pie Chart Column -->
                                <div class="col-md-6 d-flex justify-content-center">
                                    <div class="chart-container pie-chart-container">
                                        <h5 class="text-center">Active Contracts Distribution</h5>
                                        <canvas id="activeContractsChart"></canvas>
                                    </div>
                                </div>

                                <!-- Table Column -->
                                <div class="col-md-6">
                                    <table class="content-table">
                                        <thead>
                                            <tr>
                                                <th>Franchise Name</th>
                                                <th>Expiring Next Month</th>
                                                <th>Expired Contracts</th>
                                                <th>Renewal Rate (%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $franchiseResult = mysqli_query($con, $franchiseQuery);
                                            while ($row = mysqli_fetch_assoc($franchiseResult)) {
                                                $franchiseName = ucfirst(str_replace("-", " ", $row['franchisee'])); // Format name
                                                $renewalRate = ($row['renewed_contracts'] / max(1, ($row['renewed_contracts'] + $row['expired_contracts']))) * 100;
                                                
                                                echo "<tr>
                                                        <td>{$franchiseName}</td>
                                                        <td>{$row['expiring_contracts']}</td>
                                                        <td>{$row['expired_contracts']}</td>
                                                        <td>" . round($renewalRate, 2) . "%</td>
                                                    </tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>



                            <h3 class="table-title">Leasing Contracts Breakdown</h3>

                            <!-- Flexbox Row: Pie Chart on the Left, Table on the Right -->
                            <div class="row align-items-center">
                                <!-- Leasing Pie Chart Column -->
                                <div class="col-md-6 d-flex justify-content-center">
                                    <div class="chart-container pie-chart-container">
                                        <h5 class="text-center">Leasing Contracts Distribution</h5>
                                        <canvas id="leasingContractsChart"></canvas>
                                    </div>
                                </div>

                                <!-- Leasing Contracts Table Column -->
                                <div class="col-md-6">
                                    <table class="content-table">
                                        <thead>
                                            <tr>
                                                <th>Franchise Name</th>
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
                                                // Format Franchise Name
                                                $formattedFranchiseName = isset($franchiseNameMap[$row['franchisee']]) ? 
                                                    $franchiseNameMap[$row['franchisee']] : 
                                                    ucfirst(str_replace("-", " ", $row['franchisee']));

                                                // Calculate Occupancy Rate (similar to renewal rate)
                                                $occupancyRate = ($row['active_leases'] / max(1, ($row['active_leases'] + $row['expired_leases']))) * 100;

                                                echo "<tr>
                                                        <td>{$formattedFranchiseName}</td>
                                                        <td>{$row['active_leases']}</td>
                                                        <td>{$row['expiring_leases']}</td>
                                                        <td>{$row['expired_leases']}</td>
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

</body>

</html>