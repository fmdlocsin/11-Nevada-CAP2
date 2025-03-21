<?php
session_start();

include ("../../phpscripts/database-connection.php");
include ("../../phpscripts/check-login.php");
$user_data = check_login($con);

if (isset($_SERVER['REQUEST_URI'])) {
    $url = $_SERVER['REQUEST_URI'];

    $queryString = parse_url($url, PHP_URL_QUERY);

    parse_str($queryString, $params);

    $id = isset($params['id']) ? $params['id'] : '';
    $franchisee = isset($params['franchisee']) ? $params['franchisee'] : '';
    $branch = isset($params['branch']) ? urldecode($params['branch']) : '';

    $franchise = '';
    switch ($franchisee) {
        case 'potato-corner':
            $franchise = "Potato Corner";
            break;
        case 'auntie-anne':
            $franchise = "Auntie Anne's";
            break;
        case 'macao-imperial':
            $franchise = "Macao Imperial";
            break;
        default:
            $franchise = "Unknown Franchise";
            break;
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- ========= CSS ========= -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../../assets/css/navbar.css">
    <!-- <link rel="stylesheet" href="../../assets/css/inventory.css" type="text/css"> -->
    <link rel="stylesheet" href="../../assets/css/clickedInventory.css">


    <!-- ===== Boxicons CSS ===== -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <title>Inventory Module</title>
</head>

<body>
    
    <?php include '../../navbar.php'; ?>

    <section class="home">
        <header class="contractheader">
            <div class="container-header">
                <h1 class="title">Inventory</h1>
            </div>
        </header>
            <div class="container">
             <header class="header-report">Daily Ending Inventory</header>
                <div class="header-info">
                    <div class="regularText"><strong>Franchise:</strong> <?php echo $franchise ?></div>
                    <div class="regularText"><strong>Location:</strong> <?php echo $branch; ?></div>
                    <div class="regularText"><strong>Filled by:</strong> <?php echo $user_data['user_name']; ?></div>
            </div>
                    <div class="filters">
                        <!-- Upload button -->
                        <div class="filters d-flex align-items-center justify-content-end gap-3">
                            <button class="btn btn-primary" id="uploadCsvBtn">Upload CSV</button>
                            <input type="file" id="csvUpload" class="form-control w-auto d-none">
                            <!-- <span id="fileName" class="fw-bold text-secondary"></span> Placeholder for file name -->
                            <button id="save-button" class="btn btn-success">Save</button>
                        </div>

                    </div>
                    <table class="clickedInventory-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Beginning<span class="text-danger">*</span></th>
                                <th>Delivery<span class="text-danger">*</span></th>
                                <th>Waste<span class="text-danger">*</span></th>
                                <th>Sold<span class="text-danger">*</span></th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Rows will be dynamically inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="confirmationModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="subheadertext">Save?</h2>
                    </div>
                    <div class="modal-footer">
                        <button id="cancelButton" class="cancel">Cancel</button>
                        <button id="confirmSaveButton" class="save">Save</button>
                    </div>
                </div>
            </div>
            <div id="successModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="subheadertext">Save</h2>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="nextButton">Next</button>
                        <button type="button" class="btn btn-primary" id="downloadButton">Download</button>
                    </div>
                </div>
            </div>

            <!-- Toast -->
            <div class="toast-container position-fixed bottom-0 end-0 p-3">
                <div id="liveToast" class="toast text-white bg-light" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-body text-center">
                        <p class="mb-0 fw-bold"></p>
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
    <script src="../../assets/js/report-script.js"></script>
</body>

</html>