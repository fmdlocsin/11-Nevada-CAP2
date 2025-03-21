<?php
session_start();

include ("../../phpscripts/database-connection.php");
include ("../../phpscripts/check-login.php");
$user_data = check_login($con);
$dateToday = date("Y-m-d");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Expenses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../../assets/css/navbar.css">
    <link rel="stylesheet" href="../../assets/css/addExpenses.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    
    <?php include '../../navbar.php'; ?>

    <section class="home">
    <header class="contractheader">
        <div class="container-header">
            <h1 class="title">Expenses - Add Expenses</h1>
        </div>
    </header>
    <div class="container">
        <header>Add Expenses</header>
        <!-- Expense Details Form -->
        <form action="#" class="expense-form">
            <div class="details franchisee">
                <span class="title">Expense Details</span>
                <div class="fields">
                    <div class="input-field">
                        <label>Franchisee <span class="text-danger">*</span></label>
                        <select id="selectedFranchise">
                            <option disabled selected>Select Franchisee</option>
                            <option value="potato-corner">Potato Corner</option>
                            <option value="auntie-anne">Auntie Anne's</option>
                            <option value="macao-imperial">Macao Imperial Tea</option>
                        </select>

                    </div>
                    <div class="input-field">
                        <label>Location <span class="text-danger">*</span></label>
                        <select id="franchiseLocation">
                            <option value="">Select Franchisee First</option>
                        </select>
                    </div>

                    <div class="input-field">
                        <label>Name</label>
                        <input type="text" id="encoderName" placeholder="Enter Encoder's Name"
                            value="<?php echo $user_data['user_name']; ?>" disabled>
                    </div>
                    <div class="input-field">
                        <label>Date</label>
                        <input type="date" id="dateToday" value="<?php echo $dateToday; ?>" disabled>
                    </div>
                    <div class="input-field transactions">
                        <label>Expense Category <span class="text-danger">*</span></label>
                        <select id="selectedExpense">
                            <option disabled selected>Select Expense Category</option>
                            <option value="controllable-expenses">Franchisor Expenses</option>
                            <option value="non-controllable-expenses">Leasor Expenses</option>
                            <option value="other-expenses">Other Expenses</option>
                    </select>
                    </div>
                </div>

                <!-- <div class="form-group">
                    <button type="submit" class="myButton">Save</button>
                </div> -->
            </div>
        </form>
        <!-- Franchisor Expenses -->
        <form action="#" class="form-data1 controllable-form">
            <div class="details transactions">
                <span class="title">Franchisor Expenses</span>
                <div class="fields">
                    <div class="input-field transactions">
                        <label>Expense Type <span class="text-danger">*</span></label>
                        <select class="selectedExpenseType">
                            <option disabled selected>Select Expense Type</option>
                            <option value="franchiseFees">Franchise Fees</option>
                            <option value="royaltyFees">Royalty Fees</option>
                            <option value="agencyFees">Agency Fees</option>
                            <option value="others">Others</option>
                        </select>
                        <div class="input-field transactions">
                            <input type="text" class="otherExpenses" placeholder="Others:">
                        </div>
                    </div>
                </div>
                <div class="fields">
                    <div class="input-field transactions">
                        <label>Amount <span class="text-danger">*</span></label>
                        <input type="number" class="expensesAmount" placeholder="Enter Amount">
                        <textarea class="expensesDescription" placeholder="Add a description..."></textarea>
                    </div>
                </div>
            </div>
        </form>
        <!-- Leasor Expenses -->
        <form action="#" class="form-data2 controllable-form">
            <div class="details transactions">
                <span class="title">Leasor Expenses</span>
                <div class="fields">
                    <div class="input-field transactions">
                        <label>Expense Type <span class="text-danger">*</span></label>
                        <select class="selectedExpenseType">
                            <option disabled selected>Select Expense Type</option>
                            <option value="rentalsFees">Rentals</option>
                            <option value="utilitiesFees">Utilities</option>
                            <option value="maintenanceFees">Maintenance</option>
                            <option value="others">Others</option>
                        </select>
                        <div class="input-field transactions">
                            <input type="text" class="otherExpenses" placeholder="Others:">
                        </div>
                    </div>
                </div>
                <div class="fields">
                    <div class="input-field transactions">
                        <label>Amount <span class="text-danger">*</span></label>
                        <input type="number" class="expensesAmount" placeholder="Enter Amount">
                        <textarea class="expensesDescription" placeholder="Add a description..."></textarea>
                    </div>
                </div>
            </div>
        </form>
        <!-- Other Expenses -->
        <form action="#" class="form-data3 controllable-form">
            <div class="details transactions">
                <span class="title">Other Expenses</span>
                <div class="fields">
                    <div class="input-field transactions">
                        <label>Expense Type <span class="text-danger">*</span></label>
                        <input type="text" id="expensesType" placeholder="Expense Type">
                        <div class="input-field transactions">
                            <label>Purpose <span class="text-danger">*</span></label>
                            <input type="text" class="otherPurpose" placeholder="Purpose">
                        </div>
                    </div>
                </div>
                <div class="fields">
                    <div class="input-field transactions">
                        <label>Amount <span class="text-danger">*</span></label>
                        <input type="number" class="expensesAmount" placeholder="Enter Amount">
                        <textarea class="expensesDescription" placeholder="Add a description..."></textarea>
                    </div>
                </div>
            </div>
        </form>
        <div class="form-group2">
            <button type="submit" class="myButton btn-submit-expenses">Submit</button>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-box" id="modalBox">
            <div class="modal-body">
                <p id="modalMessage"></p>
            </div>
        </div>
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
    <script src="../../assets/js/add-expenses-script.js"></script>
</body>

</html>