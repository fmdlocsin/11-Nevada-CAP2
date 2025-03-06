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
                <!-- Use values matching your DB (e.g., "potato-corner", "auntie-anne", "macao-imperial") -->
                <select id="filter-franchise">
                    <option value="">All</option>
                    <option value="potato-corner">Potato Corner</option>
                    <option value="auntie-anne">Auntie Anne's</option>
                    <option value="macao-imperial">Macao Imperial</option>
                </select>
                
                <!-- New Location filter -->
                <label for="filter-location">Location:</label>
                <select id="filter-location">
                    <option value="">All</option>
                    <!-- Options will be populated dynamically based on selected franchise -->
                </select>

                <!-- Changed Category to Type -->
                <label for="filter-category">Type:</label>
                <select id="filter-category">
                    <option value="">All</option>
                    <option value="franchiseFees">Franchise Fees</option>
                    <option value="rentalsFees">Rental Fees</option>
                    <option value="royaltyFees">Royalty Fees</option>
                    <option value="maintenanceFees">Maintenance Fees</option>
                    <option value="utilitiesFees">Utilities Fees</option>
                    <option value="agencyFees">Agency Fees</option>
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
                            <th>Type</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data rows will be populated dynamically via AJAX -->
                    </tbody>
                </table>
            </section>
        </div>
    </section>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="../../assets/js/navbar.js"></script>
    <script src="../../assets/js/display-expenses-script.js"></script>
    
    <!-- Inline Script for Filters and Dynamic Branch Loading -->
    <script>
      $(document).ready(function () {
          // When the franchise filter changes, fetch branches for the location filter
          $("#filter-franchise").on("change", function () {
              var franchiseVal = $(this).val();
              if (franchiseVal) {
                  $.ajax({
                      url: "../../phpscripts/get-branches.php",
                      type: "POST",
                      data: { franchisee: franchiseVal },
                      dataType: "json",
                      success: function(response) {
                          var locationSelect = $("#filter-location");
                          locationSelect.empty().append('<option value="">All</option>');
                          if(response.status === "success"){
                              response.details.forEach(function(branch) {
                                  locationSelect.append('<option value="'+ branch.ac_id +'">'+ branch.location +'</option>');
                              });
                          }
                      },
                      error: function(xhr, status, error) {
                          console.error("Error fetching branches:", error);
                      }
                  });
              } else {
                  $("#filter-location").empty().append('<option value="">All</option>');
              }
          });
          
          // When the Generate button is clicked, fetch filtered expense data
          $("#btn-generate").on("click", function () {
              // Get filter values
              var franchise = $("#filter-franchise").val();
              var location = $("#filter-location").val();
              var category = $("#filter-category").val();
              var startDate = $("#start-date").val();
              var endDate = $("#end-date").val();
              
              // Debug: Log filter values before making AJAX request
              console.log("Filters: ", { franchise, location, category, startDate, endDate });
              
              // Call displayExpenses() with filter parameters
              displayExpenses(franchise, location, category, startDate, endDate);
          });
      });
    </script>
</body>
</html>
