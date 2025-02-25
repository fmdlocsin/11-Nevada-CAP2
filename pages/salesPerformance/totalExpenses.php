<?php
session_start();

include ("../../phpscripts/database-connection.php");
include ("../../phpscripts/check-login.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Expenses</title>

    <!-- ========= CSS ========= -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../../assets/css/navbar.css">
    <link rel="stylesheet" href="../../assets/css/expensesTypes.css">

    <!-- ===== Boxicons CSS ===== -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
  
    <?php include '../../navbar.php'; ?>

    <section class="home">
        <header class="contractheader">
            <div class="container-header">
                <h1 class="title">Expenses</h1>
            </div>
        </header>
        <div class="filter-container">
            <!-- Filters -->
            <div class="filters">
                <label for="filter-franchise">Franchisee:</label>
                <select id="filter-franchise">
                    <option value="">All</option>
                    <option value="potatoCorner">Potato Corner</option>
                    <option value="auntieAnnes">Auntie Anne's</option>
                    <option value="macaoImperial">Macao Imperial</option>
                </select>
                <label>Category:</label>
                <select id="filter-franchise">
                    <option value="">All</option>
                    <option value="controllableExpenses">Franchisor Expenses</option>
                    <option value="nonControllableExpenses">Leasor Expenses</option>
                    <option value="nonControllableExpenses">Other Expenses</option>
                </select>
                <label for="start-date">Start Date:</label>
                <input type="date" id="start-date">
                <label for="end-date">End Date:</label>
                <input type="date" id="end-date">
                <button id="btn-generate" class="resetButton">Generate</button>
                <!-- Add Expense -->
                <a href="addExpenses" class="myButton">Add Expense</a>
            </div>
        </div>
        <div class="container">
            <section id="expenses-section">
                <table class="content-table" id="totalExpensesTbl">
                    <thead>
                        <tr>
                            <th>Franchisee</th>
                            <th>Amount</th>
                            <th>Expense Category</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                       
                    </tbody>
                </table>
            </section>

        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
        crossorigin="anonymous"></script>
    <script src="../../assets/js/navbar.js"></script>
    <script src="../../assets/js/display-expenses-script.js"></script>
</body>

</html>