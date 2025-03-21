<?php
session_start();

include("../../phpscripts/database-connection.php");
include("../../phpscripts/check-login.php");

$id = isset($_GET['id']) ? $_GET['id'] : null;
$data = [];

if ($id) {
    $id = mysqli_real_escape_string($con, $id);

    $query = "SELECT * FROM agreement_contract WHERE ac_id = '$id'";
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
    <title>Edit Franchise Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/navbar.css">
    <link rel="stylesheet" href="../../assets/css/franchiseeDetails.css">
    <link rel="stylesheet" href="../../assets/css/edit-franchisedetails.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

<?php include '../../navbar.php'; ?>

<section class="home">
    <header class="contractheader">
        <div class="container-header">
            <h1 class="title">Edit Franchise Details</h1>
        </div>
    </header>

    <div class="container">
        <header>Update Franchise Details</header>

        <?php if (isset($data['error'])): ?>
            <div class="alert alert-danger"><?php echo $data['error']; ?></div>
        <?php else: ?>
            <form action="update-franchise.php" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($data['ac_id']); ?>">

                <div class="mb-3">
                    <label for="franchisee" class="form-label">Franchisee Name</label>
                    <input type="text" class="form-control readonly-input" id="franchisee" name="franchisee"
                           value="<?php echo htmlspecialchars($data['franchisee']); ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" class="form-control readonly-input" id="location" name="location"
                           value="<?php echo htmlspecialchars($data['location']); ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="classification" class="form-label">Classification</label>
                    <input type="text" class="form-control readonly-input" id="classification" name="classification"
                           value="<?php echo htmlspecialchars($data['classification']); ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="min_employees" class="form-label">Minimum Employees</label>
                    <input type="number" step="1" class="form-control" id="min_employees" name="min_employees"
                        value="<?php echo htmlspecialchars($data['min_employees']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="agreement_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="agreement_date" name="agreement_date"
                           value="<?php echo htmlspecialchars($data['agreement_date']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="franchise_fee" class="form-label">Franchise Fee</label>
                    <input type="number" step="0.01" class="form-control" id="franchise_fee" name="franchise_fee"
                           value="<?php echo htmlspecialchars($data['franchise_fee']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="franchise_package" class="form-label">Franchise Package</label>
                    <input type="number" step="0.01" class="form-control" id="franchise_package" name="franchise_package"
                           value="<?php echo htmlspecialchars($data['franchise_package']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="bond" class="form-label">Bond</label>
                    <input type="number" step="0.01" class="form-control" id="bond" name="bond"
                           value="<?php echo htmlspecialchars($data['bond']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="extra_note" class="form-label">Extra Note</label>
                    <textarea class="form-control" id="extra_note" name="extra_note"
                              rows="3"><?php echo htmlspecialchars($data['extra_note']); ?></textarea>
                </div>

                <div class="button-group">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="javascript:history.back()" class="btn-cancel">Cancel</a>
            </div>

            </form>
            
        <?php endif; ?>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="../../assets/js/navbar.js"></script>
</body>

</html>