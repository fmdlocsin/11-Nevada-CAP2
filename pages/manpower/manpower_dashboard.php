<?php
session_start();

include ("../../phpscripts/database-connection.php");
include ("../../phpscripts/check-login.php");

$username = $_SESSION['user_name'] ?? "Unknown User";

$query = "
    SELECT
        COALESCE(SUM(CASE WHEN ui.employee_status = 'assigned' THEN 1 ELSE 0 END), 0) AS assigned_count,
        COALESCE(SUM(CASE WHEN ui.employee_status = 'unassigned' THEN 1 ELSE 0 END), 0) AS unassigned_count,
        COALESCE(SUM(CASE WHEN ui.employee_status IN ('assigned', 'unassigned') THEN 1 ELSE 0 END), 0) AS total_count
    FROM
        user_information ui
    LEFT JOIN
        users_accounts ua ON ui.user_id = ua.user_id
";
$result = mysqli_query($con, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $assigned_count = $row['assigned_count'];
    $unassigned_count = $row['unassigned_count'];
    $total_count = $row['total_count'];
} else {
    $assigned_count = 0;
    $unassigned_count = 0;
    $total_count = 0;
    $error = mysqli_error($con);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manpower</title>

    <!-- ========= CSS ========= -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../../assets/css/navbar.css">
    <link rel="stylesheet" href="../../assets/css/manpower_dashboard.css">
    <link rel="stylesheet" href="../../assets/css/unassignedEmployees2.css">

    <!-- ===== Boxicons CSS ===== -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>


    <script>
        var loggedInUser = <?php echo json_encode($username); ?>;
    </script>

    
</head>

<body>
    
    <?php include '../../navbar.php'; ?>

    <section class="home">
        <header class="contractheader">
            <div class="container-header">
                <h1 class="title">Manpower</h1>
            </div>
        </header>
        <div class="container">
            <div class="dash-content">
                <div class="overview">
                    <div class="title">
                        <i class='bx bxs-tachometer'></i>
                        <span class="text">Dashboard</span>
                    </div>
                    <div class="boxes">
                        <a href="totalEmployees" id="employee-total-label" class="box box1">
                            <span class="text1"><?php echo $total_count; ?></span>
                            <span class="text">Total Employees</span>
                        </a>
                        <!-- <a href="../../pages/manpower/selectActiveBranch2" class="box box3"> -->
                        <a href="" class="box box3">
                            <span class="text1"><?php echo $assigned_count; ?></span>
                            <span class="text">Active Employees</span>
                        </a>
                        <a href="../../pages/manpower/unassignedEmployees2" class="box box3">
                            <span class="text1"><?php echo $unassigned_count; ?></span>
                            <span class="text">Unassigned Employees</span>
                        </a>

                    </div>
                </div>

                <div class="branches">
                    <div class="staffed-branches">

                        <!-- Generate Report button -->
                        <div class="d-inline-flex align-items-center gap-3">
                            <h1 class="branch-title mb-0">Fully Staffed Branches</h1>
                            <button class="btn btn-primary btn-sm d-flex align-items-center" onclick="fetchReport('fully_staffed')">
                                Generate Report
                            </button>
                        </div>


                        <a href="../../pages/manpower/manpower_fullschedule?str=potatoCorner" class="store">
                            <img class="logo" src="../../assets/images/PotCor.png" alt="Potato Corner">
                            <h4 id="store-text">Potato Corner</h4>
                        </a>
                        <a href="../../pages/manpower/manpower_fullschedule?str=auntieAnne" class="store">
                            <img class="logo" src="../../assets/images/AuntieAnn.png" alt="Auntie Anne's">
                            <h4 id="store-text">Auntie Anne's</h4>
                        </a>
                        <a href="../../pages/manpower/manpower_fullschedule?str=macaoImperial" class="store">
                            <img class="logo" src="../../assets/images/MacaoImp.png" alt="Macao Imperial Tea">
                            <h4 id="store-text">Macao Imperial Tea</h4>
                        </a>
                    </div>

                    <div class="understaffed-branches">

                        <div class="d-inline-flex align-items-center gap-3">
                            <h1 class="branch-title mb-0">Understaffed Branches</h1>
                            <!-- Understaffed Branches Report Button -->
                            <button class="btn btn-primary btn-sm" onclick="fetchReport('understaffed')">
                                Generate Report
                            </button>
                        </div>

                        <a href="../../pages/manpower/manpower_incompleteschedule?str=potatoCorner" class="store">
                            <img class="logo" src="../../assets/images/PotCor.png" alt="Potato Corner">
                            <h4 id="store-text">Potato Corner</h4>
                        </a>
                        <a href="../../pages/manpower/manpower_incompleteschedule?str=auntieAnne" class="store">
                            <img class="logo" src="../../assets/images/AuntieAnn.png" alt="Auntie Anne's">
                            <h4 id="store-text">Auntie Anne's</h4>
                        </a>
                        <a href="../../pages/manpower/manpower_incompleteschedule?str=macaoImperial" class="store">
                            <img class="logo" src="../../assets/images/MacaoImp.png" alt="Macao Imperial Tea">
                            <h4 id="store-text">Macao Imperial Tea</h4>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Report Modal -->
            <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl"> 
                <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="reportModalLabel">
                    Fully Staffed Branches Report
                    </h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Franchise Filter Dropdown -->
                    <div class="mb-3">
                    <label for="reportFranchiseeFilter" class="form-label"><strong>Filter by Franchisee:</strong></label>
                    <select id="reportFranchiseeFilter" class="form-select">
                    <option value="">All Franchisees</option>
                    <?php
                        // Define mapping of stored franchise names to display names
                        $franchiseNameMap = [
                        "auntie-anne"   => "Auntie Anne's",
                        "macao-imperial" => "Macao Imperial",
                        "potato-corner"  => "Potato Corner"
                        ];
                        // Fetch distinct franchisee names from user_information table
                        $franchiseeQuery = "SELECT DISTINCT franchisee FROM user_information";
                        $franchiseeResult = mysqli_query($con, $franchiseeQuery);
                        while ($row = mysqli_fetch_assoc($franchiseeResult)) {
                            $rawFranchisee = strtolower(trim($row['franchisee']));
                            // Skip if the franchisee value is "0" or empty
                            if ($rawFranchisee === "0" || $rawFranchisee === "") {
                                continue;
                            }
                            $formattedFranchisee = isset($franchiseNameMap[$rawFranchisee])
                                ? $franchiseNameMap[$rawFranchisee]
                                : ucfirst(str_replace("-", " ", $rawFranchisee));
                            echo "<option value='{$row['franchisee']}'>{$formattedFranchisee}</option>";
                        }
                    ?>
                    </select>

                    </div>
                    <!-- Report Data -->
                    <div id="reportData">
                    <!-- Report Data Will Be Inserted Here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" id="exportCSV">Export as CSV</button>
                    <button class="btn btn-danger" id="exportPDF">Export as PDF</button>
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
    <script src="../../assets/js/navbar.js"></script>
    <script src="../../assets/js/manage-employee-script.js"></script>

    <!-- EXPORTS SCRIPT -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>

</body>

</html>