<?php
session_start();

include("../../phpscripts/database-connection.php");
include("../../phpscripts/check-login.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inventory</title>

    <!-- ========= CSS ========= -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../../assets/css/navbar.css">
    <link rel="stylesheet" href="../../assets/css/inventory2.css">

    <!-- ===== Boxicons CSS ===== -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    
    <?php include '../../navbar.php'; ?>

    <section class="home">
        <header class="contractheader">
            <div class="container-header">
                <h1 class="title">Inventory</h1>
            </div>
        </header>
        <div class="filter-container">
            <div class="filters">
                <input type="text" placeholder="Search Branch Location" id="search">
                <input type="date" id="date">
                <select id="franchise">
                    <option value="all" selected>All</option> <!-- Show all franchises -->
                    <option value="potato-corner">Potato Corner</option>
                    <option value="auntie-anne">Auntie Anne's</option>
                    <option value="macao-imperial">Macao Imperial Tea</option>
                </select>
                <button id="new-report" data-bs-toggle="modal" data-bs-target="#modalReport">New Input</button>
            </div>
        </div>
        <div class="container">
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Franchisee</th>
                        <th>Location</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Add more rows as needed -->
                </tbody>
            </table>
        </div>

        <div id="notification-area">
            <h2>Stock Notification</h2>
            <ul id="notification-list">
            </ul>
        </div>
        <!-- content -->
    </section>
    <!-- Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast text-white bg-light" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-body text-center">
                <p class="mb-0 fw-bold"></p>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="modalReport" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 text-light fw-bold" style="background-color: #1f375d">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">New Daily Inventory</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row flex-column gap-4">
                        <div class="col text-center">
                            <button class="btn bg-secondary-subtle w-100 select-franchisee-report"
                                data-franchisee="potato-corner">
                                <img src="../../assets/images/PotCor.png" height="100%" width="150px" alt="img">
                            </button>
                        </div>
                        <div class="col text-center">
                            <button class="btn bg-secondary-subtle w-100 select-franchisee-report"
                                data-franchisee="auntie-anne">
                                <img src="../../assets/images/AuntieAnn.png" height="100%" width="150px" alt="img">
                            </button>
                        </div>
                        <div class="col text-center">
                            <button class="btn bg-secondary-subtle w-100 select-franchisee-report"
                                data-franchisee="macao-imperial">
                                <img src="../../assets/images/MacaoImp.png" height="100%" width="150px" alt="img">
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
        crossorigin="anonymous"></script>
    <script src="../../assets/js/navbar.js"></script>
    <script src="../../assets/js/report-script.js"></script>
    <script src="../../assets/js/display-inventory-notifications-script.js"></script>
</body>

</html>