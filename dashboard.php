<?php
session_start();

include ("phpscripts/database-connection.php");
include ("phpscripts/check-login.php");

// Ensure the session variable is set, otherwise provide a default value
$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "Guest";

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
                        <a href="#">
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
                    <li class="nav-link" id="inventory-link">
                        <a href="pages/inventory/inventory2">
                            <i class='bx bx-store-alt icon'></i>
                            <span class="text nav-text">Inventory</span>
                        </a>
                    </li>
                    <li class="nav-link" id="manpower-link">
                        <a href="pages/manpower/manpower_dashboard">
                            <i class='bx bx-group icon'></i>
                            <span class="text nav-text">Manpower Deployment</span>
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
    <header class="contractheader d-flex align-items-center justify-content-between">
        <div class="container-header">
            <h1 class="title">Dashboard</h1>
        </div>
        <div class="user-badge">
            <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
            <span class="user-role">Administrator</span>
        </div>
    </header>

    <div class="content" id="content-area">
        <div class="container">
        <div class="dash-content">
            <div class="overview">
            <div class="boxes-container">

                <!-- Franchising Agreement box-group with card tools integrated -->
                <div class="box-group card">
                <div class="tools">
                    <!-- Tools placed at top-right -->
                    <div class="circle">
                        <span class="dot red"></span>
                    </div>
                    <div class="circle">
                        <span class="dot yellow"></span>
                    </div>
                    <div class="circle">
                        <span class="dot green"></span>
                    </div>
                </div>
                <div class="card__content">
                    <h3 class="box-group-title">Franchising Agreement</h3>
                    <a href="admin-dashboard-brian.php" class="box box1">
                        <i class='bx bx-chart'></i>
                        <span class="text">Analytics</span>
                    </a>
                    <a href="pages/contract/franchiseeAgreement" class="box box5">
                        <i class='bx bx-folder-open'></i>
                        <span class="text">View Contracts</span>
                    </a>
                    <h4 class="sub-title">Add Contract</h4>
                    <div class="box-row">
                        <a href="pages/contract/newDocumentFranchise" class="box box7">
                            <i class='bx bx-store-alt'></i>
                            <span class="text">Agreement Contract</span>
                        </a>
                        <a href="pages/contract/newDocumentLeasing" class="box box7">
                            <i class='bx bx-building-house'></i>
                            <span class="text">Leasing Contract</span>
                        </a>
                    </div>
                </div>
                </div>

                <!-- Sales Performance box-group with card tools integrated -->
                <div class="box-group card">
                <div class="tools">
                    <div class="circle">
                        <span class="dot red"></span>
                    </div>
                    <div class="circle">
                        <span class="dot yellow"></span>
                    </div>
                    <div class="circle">
                        <span class="dot green"></span>
                    </div>
                </div>
                <div class="card__content">
                    <h3 class="box-group-title">Sales Performance</h3>
                    <a href="admin-dashboard-julia.php" class="box box2">
                        <i class='bx bx-pie-chart'></i>
                        <span class="text">Analytics</span>
                    </a>
                    <a href="pages/salesPerformance/sales" class="box box5">
                        <i class='bx bx-bar-chart'></i>
                        <span class="text">View Sales</span>
                    </a>
                    <h4 class="sub-title">Add Sales</h4>
                    <div class="box-row">
                        <a href="pages/salesPerformance/chooseFranchisee.php?tp=DineIn" class="box box7">
                            <i class='bx bx-restaurant'></i>
                            <span class="text">Dine-in</span>
                        </a>
                        <a href="pages/salesPerformance/chooseFranchisee.php?tp=TakeOut" class="box box7">
                            <i class='bx bx-shopping-bag'></i>
                            <span class="text">Takeout</span>
                        </a>
                        <a href="pages/salesPerformance/chooseFranchisee.php?tp=Delivery" class="box box7">
                            <i class='bx bx-car'></i>
                            <span class="text">Delivery</span>
                        </a>
                    </div>
                    <a href="pages/salesPerformance/totalExpenses" class="box box8">
                        <i class='bx bx-money'></i>
                        <span class="text">View Expenses</span>
                    </a>
                </div>
                </div>

                <!-- Inventory box-group with card tools integrated -->
                <div class="box-group card">
                <div class="tools">
                    <div class="circle">
                        <span class="dot red"></span>
                    </div>
                    <div class="circle">
                        <span class="dot yellow"></span>
                    </div>
                    <div class="circle">
                        <span class="dot green"></span>
                    </div>
                </div>
                <div class="card__content">
                    <h3 class="box-group-title">Inventory</h3>
                    <a href="admin-dashboard-matthew.php" class="box box4">
                        <i class='bx bx-bar-chart-square'></i>
                        <span class="text">Analytics</span>
                    </a>
                    <a href="pages/inventory/inventory2" class="box box5">
                        <i class='bx bx-spreadsheet'></i>
                        <span class="text">View Inventory</span>
                    </a>
                </div>
                </div>

                <!-- Manpower Deployment box-group with card tools integrated -->
                <div class="box-group card">
                <div class="tools">
                    <div class="circle">
                        <span class="dot red"></span>
                    </div>
                    <div class="circle">
                        <span class="dot yellow"></span>
                    </div>
                    <div class="circle">
                        <span class="dot green"></span>
                    </div>
                </div>
                <div class="card__content">
                    <h3 class="box-group-title">Manpower Deployment</h3>
                    <a href="pages/manpower/manpower_dashboard" class="box box3">
                        <i class='bx bx-street-view'></i>
                        <span class="text">Dashboard</span>
                    </a>
                    <a href="pages/manpower/totalEmployees" class="box box6">
                        <i class='bx bx-body'></i>
                        <span class="text">Employees</span>
                    </a>
                    <a href="pages/manpower/unassignedEmployees2" class="box box7">
                        <i class='bx bx-user-minus'></i>
                        <span class="text">Unassigned Employees</span>
                    </a>
                </div>
                </div>
            </div> <!-- .boxes-container -->
            </div> <!-- .overview -->
        </div> <!-- .dash-content -->
        </div> <!-- .container -->
    </div> <!-- .content -->
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
    <!-- <script src="assets/js/content.js"></script> -->
</body>

</html>