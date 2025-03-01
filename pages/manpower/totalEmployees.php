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
    <title>Total Employees</title>

    <!-- ========= CSS ========= -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../../assets/css/navbar.css">
    <link rel="stylesheet" href="../../assets/css/totalEmployees.css">

    <!-- ===== Boxicons CSS ===== -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    
    <?php include '../../navbar.php'; ?>

    <section class="home">

        <header class="contractheader">
            <div class="container-header">
                <h1 class="title">Total Employees</h1>
            </div>
        </header>

        <div class="container">
            <div class="dash-content">
                <div class="overview">
                    <div class="boxes-container">
                        <div class="box-group">
                            <h3 class="box-group-title">Employee List</h3>
                            <input type="text" class="search-box1" placeholder="Search...">
                            <div class="employee-list" id="employeeList">
                                <button type="button" class="box box1 check-employee border-0">
                                    <i class='bx bx-user'></i>
                                    <span class="text emp-name">Employee Name</span>
                                </button>
                            </div>
                            <a href="addEmployee" class="add-employee-btn">
                                <i class='bx bxs-plus-circle'></i>
                                Add Employee
                            </a>
                        </div>
                        <div class="box-group2" id="employeeDetails"></div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Unassign Employee Modal -->
        <div class="modal fade" id="unassignModal" tabindex="-1" aria-labelledby="unassignModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="unassignModalLabel">Unassign Employee</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="unassignForm">
                            <input type="hidden" id="unassignEmployeeId">
                            
                            <p class="text-center">Select a reason for unassigning:</p>
                            
                            <div class="d-flex justify-content-center gap-3">
                                <div class="box box5 unassign-option" data-value="Resign">
                                    <i class='bx bx-user-x'></i>
                                    <span class="text">Resign</span>
                                </div>
                                <div class="box box6 unassign-option" data-value="On Leave">
                                    <i class='bx bx-time'></i>
                                    <span class="text">On Leave</span>
                                </div>
                            </div>

                            <input type="hidden" name="reasonType" id="reasonType">
                            
                            <div class="mt-3">
                                <label for="reasonText" class="form-label">Additional Notes:</label>
                                <textarea class="form-control" id="reasonText" rows="3" placeholder="Enter reason"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmUnassign">Confirm</button>
                    </div>
                </div>
            </div>
        </div>



        <!-- Error Modal -->
        <div class="modal-overlay" id="modalOverlay">
            <div class="modal-box" id="modalBox">
                <div class="modal-body">
                    <p id="modalMessage"></p>
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
    <script src="../../assets/js/navbar.js"></script>
    <script src="../../assets/js/manage-employee-script.js"></script>
</body>

</html>