<?php
session_start();

include("phpscripts/database-connection.php");
include("phpscripts/check-login.php");
include("phpscripts/role_filter.php");

// Ensure the session variable is set, otherwise provide a default value
$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "Guest";

// Get the filter clause and parameter from the helper function
$filter = getAreaManagerFilter();

$branches = [];
if ($filter['clause'] !== "") {
    // We use "WHERE 1=1" so we can safely append the AND clause
    $query = "SELECT DISTINCT franchisee, location FROM agreement_contract WHERE 1=1 " . $filter['clause'] . " ORDER BY location";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $filter['param']);
    $stmt->execute();
    $result = $stmt->get_result();
    $branches = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $query = "SELECT DISTINCT franchisee, location FROM agreement_contract ORDER BY location";
    $result = $con->query($query);
    $branches = $result->fetch_all(MYSQLI_ASSOC);
}

// Group results by franchisee so that each franchisee appears once with all its locations
$groupedBranches = [];
foreach ($branches as $branch) {
    $franchisee = strtolower(str_replace(" ", "-", trim($branch['franchisee']))); // Normalize key
    $location = $branch['location'];
    if (!isset($groupedBranches[$franchisee])) {
        $groupedBranches[$franchisee] = [];
    }
    if (!in_array($location, $groupedBranches[$franchisee])) {
        $groupedBranches[$franchisee][] = $location;
    }
}

// Mapping for franchisee names to image file names; keys match your DB values
$imageMapping = [
    'potato-corner' => 'PotCor.png',
    'auntie-anne'   => 'AuntieAnn.png',
    'macao-imperial'=> 'MacaoImp.png'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- ========= CSS ========= -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/dashboard-area.css">
    <!-- ===== Boxicons CSS ===== -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script>
        var loggedInUser = <?php echo json_encode($username); ?>;
    </script>
</head>
<body>
    <nav class="sidebar close">
        <header>
            <div class="image-text">
                <span class="image">
                    <img src="assets/images/BoxLogo.png" alt="logo">
                </span>
                <div class="text header-text">
                    <span class="name">NEVADA</span>
                    <span class="profession">Management Group</span>
                </div>
            </div>
            <i class='bx bx-chevron-right toggle'></i>
        </header>
        <div class="menu-bar">
            <div class="menu">
                <li class="search-box">
                    <i class='bx bx-search icon'></i>
                    <input type="search" placeholder="Search...">
                </li>
                <ul class="menu-links">
                    <li class="nav-link active" id="dashboard-link">
                        <a href="dashboard-area">
                            <i class='bx bx-home-alt icon'></i>
                            <span class="text nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-link" id="sales-link">
                        <a href="pages/salesPerformance/sales">
                            <i class='bx bx-bar-chart-alt-2 icon'></i>
                            <span class="text nav-text">Sales Performance</span>
                        </a>
                    </li>
                    <li class="nav-link" id="expenses-link">
                        <a href="pages/salesPerformance/totalExpenses">
                            <i class='bx bx-wallet icon'></i>
                            <span class="text nav-text">Expenses</span>
                        </a>
                    </li>
                    <li class="nav-link" id="inventory-link">
                        <a href="pages/inventory/inventory2">
                            <i class='bx bx-store-alt icon'></i>
                            <span class="text nav-text">Inventory</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="bottom-content">
                <li>
                    <a href="phpscripts/user-logout.php">
                        <i class='bx bx-log-out icon'></i>
                        <span class="text nav-text">Logout</span>
                    </a>
                </li>
            </div>
        </div>
    </nav>
    <section class="home">
        <header class="contractheader d-flex align-items-center justify-content-between">
            <div class="container-header">
                <h1 class="title">Dashboard</h1>
            </div>
            <div class="user-badge">
                <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                <span class="user-role">Area Manager</span>
            </div>
        </header>
        <div class="content" id="content-area">
            <div class="container">
                <div class="dash-content">
                    <div class="overview">
                        <!-- <div class="greeting">
                            <h2>Hi, <strong>Area</strong>!</h2>
                        </div> -->
                        <div class="boxes-container">
                            <!-- Assigned Branches Section -->
                            <div class="box-group">
                                <h3 class="box-group-title2">Assigned Branches</h3>
                                <?php if (!empty($groupedBranches)): ?>
                                    <?php foreach ($groupedBranches as $franchiseeKey => $locations): ?>
                                        <div class="franchise-group">
                                            <div class="box-row">
                                                <!-- Franchisee Box -->
                                                <div class="box box2">
                                                    <?php
                                                    $imgFile = isset($imageMapping[$franchiseeKey]) ? $imageMapping[$franchiseeKey] : 'default.png';

                                                    // Fix franchisee name formatting
                                                    $franchiseeDisplayName = str_replace(
                                                        ["potato-corner", "auntie-anne", "macao-imperial"],
                                                        ["Potato Corner", "Auntie Anne's", "Macao Imperial"],
                                                        $franchiseeKey
                                                    );
                                                    ?>
                                                    <img src="assets/images/<?php echo $imgFile; ?>" alt="<?php echo htmlspecialchars($franchiseeDisplayName); ?> Logo" class="franchise-logo">
                                                    <span class="text"><?php echo htmlspecialchars($franchiseeDisplayName); ?></span>
                                                </div>

                                                <!-- Branches Container -->
                                                <div class="branches-container">
                                                    <?php foreach ($locations as $loc): ?>
                                                        <div class="branch-box">
                                                            <span class="branch-text"><?php echo htmlspecialchars($loc); ?></span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div> <!-- End of franchise-group -->
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No branches assigned.</p>
                                <?php endif; ?>
                            </div>


                            <!-- Sales Performance Section -->
                            <div class="box-group">
                                <h3 class="box-group-title">Sales Performance</h3>
                                <a href="area-dashboard-julia.php" class="box box1">
                                    <i class='bx bx-pie-chart'></i>
                                    <span class="text">Analytics</span>
                                </a>
                                <a href="pages/salesPerformance/sales" class="box box4">
                                    <i class='bx bx-bar-chart'></i>
                                    <span class="text">View Sales</span>
                                </a>
                                <h4 class="sub-title">Add Sales</h4>
                                <div class="box-row">
                                    <a href="pages/salesPerformance/chooseFranchisee.php?tp=DineIn" class="box box5">
                                        <i class='bx bx-restaurant'></i>
                                        <span class="text">Dine-in</span>
                                    </a>
                                    <a href="pages/salesPerformance/chooseFranchisee.php?tp=TakeOut" class="box box6">
                                        <i class='bx bx-shopping-bag'></i>
                                        <span class="text">Takeout</span>
                                    </a>
                                    <a href="pages/salesPerformance/chooseFranchisee.php?tp=Delivery" class="box box7">
                                        <i class='bx bx-car'></i>
                                        <span class="text">Delivery</span>
                                    </a>
                                </div>
                                <a href="pages/salesPerformance/totalExpenses" class="box box5">
                                    <i class='bx bx-money'></i>
                                    <span class="text">View Expenses</span>
                                </a>
                            </div>
                            <!-- Inventory Section -->
                            <div class="box-group">
                                <h3 class="box-group-title">Inventory</h3>
                                <a href="area-dashboard-matthew.php" class="box box1">
                                    <i class='bx bx-bar-chart-square'></i>
                                    <span class="text">Analytics</span>
                                </a>
                                <a href="pages/inventory/inventory2" class="box box7">
                                    <i class='bx bx-spreadsheet'></i>
                                    <span class="text">View Inventory</span>
                                </a>
                            </div>
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
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="assets/js/navbar.js"></script>
</body>
</html>
