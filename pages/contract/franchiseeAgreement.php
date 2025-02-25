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

    <title>Franchise Contracts</title>

    <!-- ========= CSS ========= -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../../assets/css/franchise agreement.css">
    <link rel="stylesheet" href="../../assets/css/navbar.css">

    <!-- ===== Boxicons CSS ===== -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

    <?php include '../../navbar.php'; ?>

    <section class="home">

        <header class="contractheader">
            <div class="container-header">
                <h1 class="title">Contracts</h1>
            </div>
        </header>

        <div class="filter-container">
            <!-- Filters -->
            <div class="filters">
                <label for="filter-franchise">Franchisee:</label>
                <select id="filter-franchise">
                    <option value="">All</option>
                    <option value="magalang">Potato Corner</option>
                    <option value="urdaneta">Auntie Anne's</option>
                    <option value="sm-grand">Macao Imperial</option>
                </select>

                <label for="filter-status">Status:</label>
                <select id="filter-status">
                    <option value="">All</option>
                    <option value="approved">Active</option>
                    <option value="pending">Expired</option>
                    <option value="leasing">Draft</option>
                </select>

                <button id="btn-reset" class="resetButton">Reset</button>



                <!-- New Document Button -->
                <a href="documentTypeSelection" class="myButton">New Document</a>

                <!-- Upload File Button -->
                <label for="file-upload" class="myButton">Upload File</label>
                <input type="file" id="file-upload" style="display: none;">
            </div>
        </div>
        <div class="container">
            <section id="franchise-section">
                <h2>Agreement Contract</h2>
                <div class="filters">
                    <!-- Filters can go here if you have them -->

                </div>
                <table class="content-table" id="agreementContractTbl">
                    <thead>
                        <tr>
                            <th scope="col">Franchisee</th>
                            <th scope="col">Location</th>
                            <th scope="col">Classification</th>
                            <th scope="col">Status</th>
                            <th scope="col">Days to Expire</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </section>

            <section id="leasing-section">
                <h2>Leasing Contract</h2>
                <div class="filters">
                    <!-- Filters can go here if you have them -->
                </div>
                <table class="content-table" id="leasingContractTbl">
                    <thead>
                        <tr>
                            <th>Franchisee</th>
                            <th>Classification</th>
                            <th>Status</th>
                            <th>Days to Expire</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </section>

            <!-- Notification Area -->
            <div id="notification-area">
                <h2>Notifications</h2>
                <ul id="notification-list"></ul>
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
    <script src="../../assets/js/navbar.js"></script>
    <!-- <script src="../../assets/js/content.js"></script> -->
    <script src="../../assets/js/leasing-contract-script.js"></script>
    <script src="../../assets/js/agreement-contract-script.js"></script>
    <script src="../../assets/js/notification-contract-script.js"></script>
</body>

</html>