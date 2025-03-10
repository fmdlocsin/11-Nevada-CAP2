<?php
session_start();

include("../../phpscripts/database-connection.php");
include("../../phpscripts/check-login.php");

$id = isset($_GET['id']) ? $_GET['id'] : null;
$data = [];

if ($id) {
    $id = mysqli_real_escape_string($con, $id);

    $query = "SELECT * FROM lease_contract WHERE lease_id = '$id'";
    $result = mysqli_query($con, $query);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
        } else {
            $data['error'] = "No record found with ID: $id";
        }
    } else {
        $data['error'] = "Database query failed: " . mysqli_error($con);
    }
} else {
    $data['error'] = "ID not provided in the URL.";
}

function formatFranchiseeName($name)
{
    return strtoupper(str_replace('-', ' ', $name));
}
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
    <link rel="stylesheet" href="../../assets/css/leasingDetails.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

    <?php include '../../navbar.php'; ?>

    <section class="home">

        <header class="contractheader">
            <div class="container-header">
                <h1 class="title">Leasing Details</h1>
            </div>
        </header>

        <div class="container">
            <div class="contract-content">
                <div class="contract-title">LEASE CONTRACT</div>
                <div class="contract-subtitle franchisee-name">
                    <?php echo strtoupper(str_replace('-', ' ', $data['franchisee'])); ?>
                </div>

                <div class="contract-subtitle">
                    <span>LEASE PERIOD:</span>
                </div>
                <div class="detail-item">
                    <span>Lease Start Date:</span>
                    <p><?php echo $data['start_date']; ?></p>
                    <span>Lease End Date:</span>
                    <p><?php echo $data['end_date']; ?> </p>
                </div>

                <div class="contract-subtitle">
                    <span>RENT:</span>
                </div>
                <div class="detail-grid">
                    <div class="detail-row">
                        <span>Space Number:</span>
                        <p><?php echo $data['space_number']; ?></p>
                    </div>
                    <div class="detail-row">
                        <span>Area:</span>
                        <p><?php echo $data['area']; ?> sqm</p>
                    </div>
                    <div class="detail-row">
                        <span>Classification:</span>
                        <p><?php echo $data['classification']; ?></p>
                    </div>
                    <div class="detail-row">
                        <span>Rent:</span>
                        <p>PHP <?php echo number_format($data['rent'], 2); ?></p>
                    </div>
                    <div class="detail-row">
                        <span>Percentage Rent:</span>
                        <p><?php echo $data['percentage_rent']; ?>%</p>
                    </div>
                    <div class="detail-row">
                        <span>Minimum Rent:</span>
                        <p>PHP <?php echo number_format($data['minimum_rent'], 2); ?></p>
                    </div>
                </div>


                <div class="contract-subtitle">
                    <span>FEES:</span>
                </div>
                <div class="detail-item">
                    <span>Additional Fee:</span>
                    <p>₱ <?php echo number_format($data['additional_fee'], 2); ?></p>
                    <span>Total Monthly Dues:</span>
                    <p>₱ <?php echo number_format($data['total_monthly_dues'], 2); ?></p>
                    <span>Lease Deposit:</span>
                    <p>₱ <?php echo number_format($data['lease_deposit'], 2); ?></p>
                </div>

                <div class="contract-subtitle">
                    <span>PARTIES INVOLVED:</span>
                </div>
                <div class="detail-item">
                    <span>Lessor Name:</span>
                    <p><?php echo $data['lessor_name1']; ?></p>
                    <span>Lessor Address:</span>
                    <p><?php echo $data['lessor_address1']; ?></p>
                    <span>Lessee Name:</span>
                    <p><?php echo $data['lessor_name2']; ?></p>
                    <span>Lessee Address:</span>
                    <p><?php echo $data['lessor_address2']; ?></p>
                </div>

                <div class="contract-subtitle">
                    <span>EXTRA:</span>
                </div>
                <div class="detail-item">
                    <p><?php echo $data['extra_note']; ?></p>
                </div>


                <!-- <div class="contract-subtitle">
                    <span>Notary Public's Seal:</span>
                </div>
                <div class="detail-item">
                    <img src="../../assets/images/notarySeals/<?php echo $data['notary_public_seal']; ?>" alt="img">
                </div>
            </div>
            <div class="button-group">
                <button class="myButton">Edit Details</button>
                <button class="myButton">Print Contract</button>
            </div> -->
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
    <!-- <script src="../../assets/js/content.js"></script> -->
    <script src="../../assets/js/leasing-contract-script.js"></script>
</body>

</html>