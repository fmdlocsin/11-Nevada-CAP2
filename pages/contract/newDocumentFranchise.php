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
            <header>Franchisee Agreement Contract Details</header>
            <form id="agreement-document-form">
                <!-- Franchise Selection -->
                <div class="form-group-1">
                    <label for="franchise">FRANCHISEE: <span class="text-danger">*</span></label>
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

                <!-- License granted -->
                <div class="form-group-1">
                    <label for="licenseGranted">LICENSE GRANTED:</label>
                    <div class="input-field">
                        <label for="classification" class="subLabel">Classification: <span class="text-danger">*</span> </label>
                        <select id="classification" name="classification" class="short-input" required>
                            <option value="">-- Select Classification --</option>
                            <option value="In Line Store">In Line Store</option>
                            <option value="Counter Type">Counter Type</option>
                            <option value="Kiosk">Kiosk</option>
                        </select>
                    </div>

                    <!-- Rights granted -->
                    <div class="form-group checkbox-group">
                        <div>
                            <label for="non-exclusive">
                                <input type="checkbox" id="non-exclusive" name="rightsGranted[]" value="non-exclusive">
                                Non-exclusive right to operate a "Franchisee name" OUTLET
                            </label>
                        </div>
                        <div>
                            <label for="use-trademarks">
                                <input type="checkbox" id="use-trademarks" name="rightsGranted[]"
                                    value="use-trademarks">
                                Right to use the trademark "Franchisee name" and other proprietary marks
                            </label>
                        </div>
                        <div>
                            <label for="sell-products">
                                <input type="checkbox" id="sell-products" name="rightsGranted[]" value="sell-products">
                                Right to sell proprietary products of "Franchisee name" at the approved location
                            </label>
                        </div>
                    </div>
                </div>

                <!-- TERM OF FRANCHISE -->
                <!-- Date Details, Franchise Term, and Location -->
                <div class="form-group-1">
                    <label for="termFranchise">TERM OF FRANCHISE:</label>
                    <div class="input-field">

                        <!-- <label for="franchise-term" class="subLabel">Franchise Term (in years):</label>
                        <input type="number" id="franchise-term" name="franchiseTerm" class="short-input" min="1"
                            required> -->
                        <label for="agreement-start" class="subLabel">This Agreement entered into on this: <span class="text-danger">*</span></label>
                        <input type="date" id="agreement-start" name="agreementStart" class="short-input" required>

                        <label for="agreement-date" class="subLabel">End Date: <span class="text-danger">*</span></label>
                        <input type="date" id="agreement-date" name="agreementDate" class="short-input" required>
                    </div>
                </div>

                <!-- LOCATION -->
                <div class="form-group-1">

                    <label for="location">LOCATION:</label>

                    <div class="input-field">
                        <div class="location-input">
                            <label for="location" class="locLabel">Location: <span class="text-danger">*</span></label>
                            <input type="text" id="location" name="location"
                                class="long-input">

                            <label for="areaCcode" class="locLabel">Area Code: <span class="text-danger">*</span></label>
                            <input type="number" id="area-code" name="areacode" min="0" step="any">
                            
                        </div>
                    </div>
                </div>

              <!-- MINIMUM EMPLOYEES -->
                <div class="form-group-1">
                    <label for="minEmployees">MINIMUM EMPLOYEES:</label>
                    <div class="input-field">
                        <label for="minEmployees" class="locLabel">
                            Number of Employees Required: <span class="text-danger">*</span>
                        </label>
                        <input type="number" id="minEmployees" name="minEmployees" min="2" step="1" class="short-input" value="2" style="width: 80px;" required>
                        <small class="text-muted" style="font-size: 12px; margin-top: 2px;">Minimum count cannot be below 2.</small>
                    </div>
                </div>




                <!-- FRANCHISE FEE -->
                <div class="form-group-1">

                    <label for="franchiseFee">FEES:</label>
                    <div class="form-group payment-details">
                        <label for="franchise-fee">Franchise Fee (PHP): <span class="text-danger">*</span></label>
                        <input type="number" id="franchise-fee" name="franchiseFee" min="0" step="any">
                        <textarea id="franchise-fee-note" name="franchiseFeeNote"
                            placeholder="Add a note..."></textarea>

                        <br>
                        <!-- Franchise Package -->
                        <label for="franchise-package">Franchise Package (PHP): <span class="text-danger">*</span></label>
                        <input type="number" id="franchise-package" name="franchisePackage" min="0" step="any">
                        <textarea id="franchise-package-note" name="franchisePackageNote"
                            placeholder="Add a note..."></textarea>

                        <br>
                        <!-- Bond -->
                        <label for="bond">Bond (PHP) <span class="note">(waived in existing
                                store):</span> <span class="text-danger">*</span></label>
                        <input type="number" id="bond" name="bond" min="0" step="any">
                        <textarea id="bond-note" name="bondNote" placeholder="Add a note..."></textarea>

                    </div>

                </div>

                <!-- EXTRA -->
                <div class="form-group-1">
                    <label for="extra-franchise">EXTRA:</label>

                    <div class="form-group payment-details">
                        <textarea id="extra-note-franchise" name="extraNoteFranchise"
                            placeholder="Add a note..."></textarea>
                    </div>
                </div>


                <div class="form-group-2">
                    <label for="notarizedFranchise">NOTARIZATION</label>
                    <div class="input-field">
                        <div class="form-row">
                            <div class="input-group">
                                <label for="franchisor" class="subLabel">Franchisor: <span class="text-danger">*</span></label>
                                <input type="text" id="franchisor" name="franchisor" class="notarizationInput">
                            </div>
                            <div class="input-group">
                                <label for="franchisee" class="subLabel">Franchisee: <span class="text-danger">*</span></label>
                                <input type="text" id="franchisee" name="franchisee" class="notarizationInput">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="input-group">
                                <label for="franchisor-rep" class="subLabel">Represented by: <span class="text-danger">*</span></label>
                                <input type="text" id="franchisor-rep" name="franchisorRep" class="notarizationInput">
                            </div>
                            <div class="input-group">
                                <label for="franchisee-rep" class="subLabel">Represented by: <span class="text-danger">*</span></label>
                                <input type="text" id="franchisee-rep" name="franchiseeRep" class="notarizationInput">
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Notarization Details -->
                <div class="form-group-1">
                    <div class="form-group notarization-details">
                        <label for="notary-seal-franchise">Notary Public's Seal: <span class="text-danger"></span></label>
                        <input type="file" id="notary-seal-franchise" name="notarySealFranchise">
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
    <script src="../../assets/js/agreement-contract-script.js"></script>
</body>

</html>