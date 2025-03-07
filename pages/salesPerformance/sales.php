<?php
session_start();
include("../../phpscripts/database-connection.php");
include("../../phpscripts/check-login.php");
include("../../phpscripts/role_filter.php");

// Get the role-based filter for area managers
$filter = getAreaManagerFilter();

if ($filter['clause'] !== "") {
    // Use "WHERE 1=1" so we can safely append the additional filter clause
    $query = "SELECT sr.*, ac.location 
              FROM sales_report sr
              LEFT JOIN agreement_contract ac ON sr.ac_id = ac.ac_id
              WHERE 1=1 " . $filter['clause'] . " 
              ORDER BY sr.date_added DESC, sr.report_id DESC LIMIT 10";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $filter['param']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $data['error'] = "No record found";
    }
    $stmt->close();
} else {
    // For non-area managers:
    $query = "SELECT sr.*, ac.location 
              FROM sales_report sr
              LEFT JOIN agreement_contract ac ON sr.ac_id = ac.ac_id
              ORDER BY sr.date_added DESC, sr.report_id DESC LIMIT 10";
    $result = mysqli_query($con, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        $data['error'] = "No record found";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Performance</title>
    <!-- ========= CSS ========= -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../../assets/css/navbar.css">
    <link rel="stylesheet" href="../../assets/css/sales.css">
    <!-- ===== Boxicons CSS ===== -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../../navbar.php'; ?>
    <section class="home">
        <header class="contractheader">
            <div class="container-header">
                <h1 class="title">Sales Report</h1>
            </div>
        </header>
        <div class="filter-container">
            <!-- Filters -->
            <div class="filters">
                <label for="filter-franchise">Franchisee:</label>
                <select id="filter-franchise">
                    <option value="all">All</option>
                    <option value="potato-corner">Potato Corner</option>
                    <option value="auntie-anne">Auntie Anne's</option>
                    <option value="macao-imperial">Macao Imperial</option>
                </select>
                <label for="filter-location">Location:</label>
                <select id="filter-location" disabled>
                    <option value="">Select Location</option>
                </select>
                <label for="start-date">Start Date:</label>
                <input type="date" id="start-date">
                <label for="end-date">End Date:</label>
                <input type="date" id="end-date">
                <button id="btn-generate" class="resetButton">Generate</button>
            </div>
        </div>
        <div class="container">
            <div class="dash-content">
                <div class="overview">
                    <div class="title">
                        <i class='bx bx-wallet-alt'></i>
                        <span class="text">Transaction Type</span>
                    </div>
                    <div class="boxes">
                        <a href="#" class="box box1">
                            <i class='bx bx-list-ul'></i>
                            <span class="text">ALL</span>
                        </a>
                        <a href="chooseFranchisee?tp=DineIn" class="box box3">
                            <i class='bx bx-restaurant'></i>
                            <span class="text">Dine-In</span>
                        </a>
                        <a href="chooseFranchisee?tp=TakeOut" class="box box3">
                            <i class='bx bx-walk'></i>
                            <span class="text">Take-Out</span>
                        </a>
                        <a href="chooseFranchisee?tp=Delivery" class="box box3">
                            <i class='bx bx-trip'></i>
                            <span class="text">Delivery</span>
                        </a>
                    </div>
                </div>
            </div>
            <section id="sales-section">
                <table class="content-table">
                    <thead>
                        <tr>
                            <th>Franchisee</th>
                            <th>Location</th>
                            <th>Transaction Type</th>
                            <th>Total Sales</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="salesTableBody">
                        <?php if (!empty($data) && !isset($data['error'])) {
                            foreach ($data as $row) {
                                $transactions = explode(',', $row['grand_total']);

                                // Determine franchise image based on franchise name
                                $franchise = strtolower($row['franchisee']);
                                $franchise_image = 'default-image.png';
                                switch ($franchise) {
                                    case "potato-corner":
                                        $franchise_image = "PotCor.png";
                                        break;
                                    case "auntie-anne":
                                        $franchise_image = "AuntieAnn.png";
                                        break;
                                    case "macao-imperial":
                                        $franchise_image = "MacaoImp.png";
                                        break;
                                }
                                ?>
                                <tr class="btn-si-data" data-rid="<?php echo $row['report_id']; ?>">
                                    <td>
                                        <img class="franchise-logo" src="../../assets/images/<?php echo $franchise_image; ?>"
                                             alt="Franchise Image">
                                    </td>
                                    <td><?php echo $row['location']; ?></td>
                                    <td><?php echo ucwords($row['services']); ?></td>
                                    <td>₱ <?php echo number_format(end($transactions), 2, '.', ','); ?></td>
                                    <td><?php echo $row['date_added']; ?></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="5"><?php echo isset($data['error']) ? $data['error'] : 'No records found'; ?></td>
                            </tr>
                        <?php } ?>
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
    <script>
        $(document).ready(function () {
            $(document).on("click", ".btn-si-data", function () {
                var id = $(this).data("rid");
                window.location.href = "salesReport.php?id=" + id;
            });
        });
        $(document).ready(function () {
            // When the franchise dropdown changes, fetch its locations
            $("#filter-franchise").on("change", function(){
                var selectedFranchise = $(this).val();
                var locationDropdown = $("#filter-location");
                if (!selectedFranchise || selectedFranchise === 'all') {
                    locationDropdown.prop("disabled", true).html('<option value="">-- Select Location --</option>');
                    return;
                }
                $.ajax({
                    url: "../../phpscripts/getSalesLocation.php",
                    method: "GET",
                    data: { franchise: selectedFranchise },
                    success: function(response) {
                        try {
                            var locations = JSON.parse(response);
                            locationDropdown.empty();
                            if (locations.length > 0) {
                                locationDropdown.append('<option value="all">All</option>');
                                $.each(locations, function(index, loc) {
                                    locationDropdown.append('<option value="' + loc + '">' + loc + '</option>');
                                });
                                locationDropdown.prop("disabled", false);
                            } else {
                                locationDropdown.append('<option value="">No locations found</option>');
                                locationDropdown.prop("disabled", true);
                            }
                        } catch(e) {
                            console.error("Error parsing JSON:", e);
                            locationDropdown.empty().append('<option value="">Error loading locations</option>');
                            locationDropdown.prop("disabled", true);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching locations:", error);
                    }
                });
            });
            $("#btn-generate").on("click", function(){
                var franchise = $("#filter-franchise").val();
                var location = $("#filter-location").val();
                var startDate = $("#start-date").val();
                var endDate = $("#end-date").val();
                
                $.ajax({
                    url: "../../phpscripts/filterSales.php",
                    method: "GET",
                    data: { 
                        franchise: franchise, 
                        location: location,
                        start: startDate, 
                        end: endDate 
                    },
                    success: function(response) {
                        $("#salesTableBody").html(response);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching filtered data: ", error);
                    }
                });
            });
        });
    </script>
</body>
</html>
