<?php

session_start();

include ("phpscripts/database-connection.php");
include ("phpscripts/check-login.php");

// Validate connection
if (!isset($con) || !$con) {
    die("Database connection failed: " . mysqli_connect_error());
}


// Fetch Active Franchise Contracts (Using agreement_contract table)
$activeContractsQuery = "SELECT COUNT(*) as total FROM agreement_contract WHERE status = 'active'";
$activeContractsResult = mysqli_query($con, $activeContractsQuery);
$activeContracts = ($activeContractsResult) ? mysqli_fetch_assoc($activeContractsResult)['total'] : 0;

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


// Close the database connection
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
    <link rel="stylesheet" href="assets/css/contract-dashboard.css">
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
                        <div class="container">
                            <h2 class="dashboard-title">Franchise Agreement Monitoring</h2>

                            <!-- KPI Cards -->
                            <div class="row kpi-row">
                                <div class="col-md-4 kpi-col">
                                    <div class="card kpi-card">
                                        <div class="card-body">
                                            <i class="kpi-icon ni ni-chart-bar-32"></i> <!-- Icon -->
                                            <h4>Active Contracts</h4>
                                            <h2 class="kpi-number" id="activeContracts"><?php echo $activeContracts; ?></h2>
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
                                    <div class="card kpi-card">
                                        <div class="card-body">
                                            <i class="kpi-icon ni ni-check-bold"></i> <!-- Icon -->
                                            <h4>Contracts Renewed</h4> <!-- Shortened Title -->
                                            <h2 class="kpi-number" id="renewedContracts"><?php echo $renewedContracts; ?></h2>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            
                            <!-- Graphs -->
                            <div class="chart-container">
                                <canvas id="contractRenewalChart"></canvas>
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
</body>

</html>