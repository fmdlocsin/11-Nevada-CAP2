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
    <title>New Document Franchise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../../assets/css/navbar.css">
    <link rel="stylesheet" href="../../assets/css/newDocumentFranchise.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

    <?php include '../../navbar.php'; ?>

    <section class="home">

        <header class="contractheader">
            <div class="container-header">
                <h1 class="title">Create New Document</h1>
            </div>
        </header>

        <div class="container">
            <!-- Leasing part -->
            <header>Leasing Contract Details</header>
            <form id="leasing-document-form">
                <!-- Franchise Selection -->
                <div class="form-group">
                    <label for="franchise">FRANCHISEE: <span class="text-danger">*</span> </label>
                    <div id="franchise-buttons">
                        <button type="button" class="btn-option franchise-button" data-value="potato-corner">
                            <img src="../../assets/images/PotCor.png" alt="Potato Corner">
                            <span>Potato Corner</span>
                        </button>
                        <button type="button" class="btn-option franchise-button" data-value="auntie-anne">
                            <img src="../../assets/images/AuntieAnn.png" alt="Auntie Anne's">
                            <span>Auntie Anne's</span>
                        </button>
                        <button type="button" class="btn-option franchise-button" data-value="macao-imperial">
                            <img src="../../assets/images/MacaoImp.png" alt="Macao Imperial">
                            <span>Macao Imperial</span>
                        </button>
                    </div>
                    <input type="hidden" id="franchise" name="franchise" required>
                </div>


                <!-- <div class="form-group">
                <label for="lessee-representative">Lessee Representative Name:</label>
                <input type="text" id="lessee-representative" name="lesseeRepresentative" required>
            </div> -->


                <!-- TERM OF LEASE -->
                <!-- Date Details, Franchise Term, and Location -->
                <div class="form-group-1">
                    <label for="termLeaseFranchise">LEASE PERIOD:</label>
                    <div class="input-field">
                        <label for="lease-start-date" class="subLabel">Lease Start Date: <span class="text-danger">*</span> </label>
                        <input type="date" id="lease-start-date" name="leaseStartDate" required>
                        <label for="lease-end-date" class="subLabel">Lease End Date: <span class="text-danger">*</span> </label>
                        <input type="date" id="lease-end-date" name="leaseEndDate" required>
                    </div>
                </div>

                <div class="form-group-5">
                    <label for="rent" class="left-label">RENT:</label>
                    <div class="input-field">
                        <div class="form-row">
                            <div class="input-group">
                                <label for="space-number" class="subLabel">Space Number: <span class="text-danger">*</span> </label>
                                <input type="text" id="space-number" name="spaceNumber" class="notarizationInput"
                                    required>
                            </div>
                            <div class="input-group">
                                <label for="area" class="subLabel">Area (sqm): <span class="text-danger">*</span> </label>
                                <input type="number" id="area" name="area" class="notarizationInput" step="any"
                                    required>
                            </div>
                            <div class="input-group">
                                <label for="classification" class="subLabel">Classification: <span class="text-danger">*</span> </label>
                                <select id="classification" name="classification" class="notarizationInput" required>
                                    <option value="">-- Select Classification --</option>
                                    <option value="In Line Store">In Line Store</option>
                                    <option value="Counter Type">Counter Type</option>
                                    <option value="Kiosk">Kiosk</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="input-group">
                                <label for="rent" class="subLabel">Rent (PHP/sqm): <span class="text-danger">*</span> </label>
                                <input type="number" id="rent" name="rent" class="notarizationInput" step="any"
                                    required>
                            </div>
                            <div class="input-group">
                                <label for="percentage-rent" class="subLabel">Percentage Rent (%): <span class="text-danger">*</span> </label>
                                <input type="number" id="percentage-rent" name="percentageRent"
                                    class="notarizationInput" step="any" required>
                            </div>
                            <div class="input-group">
                                <label for="minimum-rent" class="subLabel">Minimum Rent (PHP/sqm): <span class="text-danger">*</span> </label>
                                <input type="number" id="minimum-rent" name="minimumRent" class="notarizationInput"
                                    step="any" required>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- LEASE FEE -->
                <div class="form-group-1">

                    <label for="franchiseFee">FEES:</label>
                    <div class="form-group payment-details">
                        <label for="lease-fee">Additional Fee (PHP): <span class="text-danger">*</span> </label>
                        <input type="number" id="lease-fee" name="leaseFee" min="0" step="any">
                        <textarea id="franchise-fee-note" name="franchiseFeeNote"
                            placeholder="Add a note..."></textarea>

                        <br>
                        <!-- Monthly Dues -->
                        <label for="total-monthly-dues">Total Monthly Dues (PHP): <span class="text-danger">*</span> </label>
                        <input type="number" id="total-monthly-dues" name="totalMonthlyDues" step="any" required>
                        <textarea id="total-monthly-dues-note" name="totalMonthlyDues"
                            placeholder="Add a note..."></textarea>

                        <br>
                        <!-- Lease Deposit -->
                        <label for="lease-deposit">Lease Deposit (PHP): <span class="text-danger">*</span> </label>
                        <input type="number" id="lease-deposit" name="leaseDeposit" step="any" required>
                        <textarea id="lease-deposit-note" name="depositNote" placeholder="Add a note..."></textarea>
                    </div>
                </div>

                <!-- Parties Involved -->
                <div class="form-group-3">
                    <label for="partiesInvolved">PARTIES INVOLVED</label>
                    <div class="input-field">
                        <div class="form-row">
                            <div class="input-group">
                                <label for="lessor-name" class="subLabel">Lessor Name: <span class="text-danger">*</span> </label>
                                <input type="text" id="lessor-name" name="lessorName" class="notarizationInput">
                            </div>
                            <div class="input-group">
                                <label for="lessee-name" class="subLabel">Lessee Name: <span class="text-danger">*</span> </label>
                                <input type="text" id="lessee-name" name="lesseeName" class="notarizationInput">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="input-group">
                                <label for="lessor-address" class="subLabel">Lessor Address: <span class="text-danger">*</span> </label>
                                    <input type="text" id="lessor-address" name="lessorAddress">
                                    <!-- <i class="bx bx-search-alt icon"></i> -->
                            </div>
                            <div class="input-group">
                                <label for="lessee-address" class="subLabel">Lessee Address: <span class="text-danger">*</span> </label>
                                    <input type="text" id="lessee-address" name="lesseeAddress">
                                    <!-- <i class="bx bx-search-alt icon"></i> -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EXTRA -->
                <div class="form-group-1">
                    <label for="extra-leasing">EXTRA:</label>

                    <div class="form-group payment-details">
                        <textarea id="extra-note-leasing" name="extraNoteLeasing"
                            placeholder="Add a note..."></textarea>
                    </div>
                </div>
                <!-- Notarization Details -->
                <div class="form-group-1">
                    <div class="form-group notarization-details">
                        <label for="notary-seal-leasing">Notary Public's Seal: <span class="text-danger"></span></label>
                        <input type="file" id="notary-seal-leasing" name="notarySealLeasing">
                        <p class="hint">Upload Notary Public's Seal</p>
                    </div>
                </div>
                <!-- Submit and Save Button -->
                <div class="form-group button-group">
                    <button type="button" class="myButton">Create Document</button>
                </div>
            </form>
        </div>

    </section>

    <!-- Modal -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-box" id="modalBox">
            <div class="modal-body">
                <p id="modalMessage"></p>
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
    <script src="../../assets/js/leasing-contract-script.js"></script>
</body>

</html>