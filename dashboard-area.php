<?php
session_start();

include ("phpscripts/database-connection.php");
include ("phpscripts/check-login.php");
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

    <link rel="stylesheet" href="assets/css/dashboard-area.css">
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
                            <a href="dashboard-area">
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
            <h1 class="title">Dashboard</h1>
        </div>
    </header>
        

        <div class="content" id="content-area">
            <div class="container">
                <div class="dash-content">
                    <div class="overview">

                    <div class="greeting">
                    <h2>Hi, <strong>Area</strong>!</h2>
                    </div>

                        <div class="boxes-container">
                                
                                <div class="box-group">
                                    <h3 class="box-group-title">Assigned Branches</h3>
                                    <div class="box-row">
                                        <a href=# class="box box2">
                                            <img src="assets/images/PotCor.png" alt="Potato Corner Logo" class="franchise-logo">
                                            <span class="text">Potato Corner</span>
                                        </a>
                                        <a href=# class="box box3">
                                            <span class="text">>Branches<</span>
                                        </a>
                                    </div>

                                    <div class="box-row">
                                        <a href=# class="box box2">
                                            <img src="assets/images/AuntieAnn.png" alt="Auntie Anne's Logo" class="franchise-logo">
                                            <span class="text">Auntie Anne's</span>
                                        </a>
                                        <a href=# class="box box3">
                                            <span class="text">>Branches<</span>
                                        </a>
                                    </div>

                                    <div class="box-row">
                                        <a href=# class="box box2">
                                            <img src="assets/images/MacaoImp.png" alt="Macao Imperial Logo" class="franchise-logo">
                                            <span class="text">Macao Imperial</span>
                                        </a>
                                        <a href=# class="box box3">
                                            <span class="text">>Branches<</span>
                                        </a>
                                    </div>

                                </div>
                                <div class="box-group">
                                    <h3 class="box-group-title">Sales Performance</h3>
                                    <a href="dashboard2/dashboard-contract2.php" class="box box1">
                                        <i class='bx bx-chart'></i>
                                        <span class="text">Analytics</span>
                                    </a>
                                    
                                    <!-- View Sales -->
                                    <a href="pages/salesPerformance/sales" class="box box4">
                                        <i class='bx bx-bar-chart'></i>
                                        <span class="text">View Sales</span>
                                    </a>

                                    <!-- Add Sales Title -->
                                    <h4 class="sub-title">Add Sales</h4>

                                    <!-- Add Sales: Dine-in, Takeout, Delivery -->
                                    <div class="box-row">
                                        <a href="pages/salesPerformance/chooseFranchisee.php?tp=DineIn" class="box box5">
                                            <i class='bx bx-restaurant'></i>
                                            <span class="text">Dine-in</span>
                                        </a>
                                        <a href="pages/salesPerformance/chooseFranchisee.php?tp=TakeOut" class="box box6">
                                            <i class='bx bx-shopping-bag'></i>
                                            <span class="text">Takeout</span>
                                        </a>
                                        <a href="pages/salesPerformance/chooseFranchisee.php?tp=Delivery" class="box box7">
                                            <i class='bx bx-car'></i>
                                            <span class="text">Delivery</span>
                                        </a>
                                    </div>

                                    <!-- View Expenses -->
                                        <a href="pages/salesPerformance/totalExpenses" class="box box5">
                                            <i class='bx bx-money'></i>
                                            <span class="text">View Expenses</span>
                                        </a>
                                    </div>

                                    <div class="box-group">
                                        <h3 class="box-group-title">Inventory</h3>
                                        <a href="dashboard2/dashboard-contract2.php" class="box box1">
                                            <i class='bx bx-chart'></i>
                                            <span class="text">Analytics</span>
                                        </a>
                                        <a href="pages/inventory/inventory2" class="box box7">
                                            <i class='bx bx-spreadsheet'></i>
                                            <span class="text">View Inventory</span>
                                        </a>
                                    </div>

                                    <!-- <div class="box-group">
                                        <h3 class="box-group-title">Manpower Deployment</h3>
                                        <a href="pages/manpower/manpower_dashboard" class="box box5">
                                            <i class='bx bx-street-view'></i>
                                            <span class="text">Dashboard</span>
                                        </a>
                                        <a href="pages/manpower/totalEmployees" class="box box6">
                                            <i class='bx bx-body'></i>
                                            <span class="text">Employees</span>
                                        </a>
                                        <a href="pages/manpower/unassignedEmployees2" class="box box6">
                                            <i class='bx bx-user-minus'></i>
                                            <span class="text">Unassigned Employees</span>
                                        </a>
                                    </div> -->

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
        <!-- <script src="assets/js/content.js"></script> -->
    </body>


</html>