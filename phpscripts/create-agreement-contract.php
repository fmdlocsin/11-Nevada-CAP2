<?php
session_start();

include("database-connection.php");
include("check-login.php");
$user_data = check_login($con);
$user_id_logged_in = $user_data['user_id'];

$data = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $docStatus = $_POST['docStatus'];
    $franchise = isset($_POST['franchise']) ? $_POST['franchise'] : '';
    $classification = isset($_POST['classification']) ? $_POST['classification'] : '';
    $rightsGranted = isset($_POST['rightsGranted']) ? $_POST['rightsGranted'] : [];
    $agreementStart = isset($_POST['agreementStart']) ? $_POST['agreementStart'] : '';
    $agreementDate = isset($_POST['agreementDate']) ? $_POST['agreementDate'] : '';
    $location = isset($_POST['location']) ? $_POST['location'] : '';
    $franchiseFee = isset($_POST['franchiseFee']) ? $_POST['franchiseFee'] : '';
    $franchiseFeeNote = isset($_POST['franchiseFeeNote']) ? $_POST['franchiseFeeNote'] : '';
    $franchisePackage = isset($_POST['franchisePackage']) ? $_POST['franchisePackage'] : '';
    $franchisePackageNote = isset($_POST['franchisePackageNote']) ? $_POST['franchisePackageNote'] : '';
    $bond = isset($_POST['bond']) ? $_POST['bond'] : '';
    $bondNote = isset($_POST['bondNote']) ? $_POST['bondNote'] : '';
    $extraNoteFranchise = isset($_POST['extraNoteFranchise']) ? $_POST['extraNoteFranchise'] : '';
    $franchisor = isset($_POST['franchisor']) ? $_POST['franchisor'] : '';
    $franchisee = isset($_POST['franchisee']) ? $_POST['franchisee'] : '';
    $franchisorRep = isset($_POST['franchisorRep']) ? $_POST['franchisorRep'] : '';
    $franchiseeRep = isset($_POST['franchiseeRep']) ? $_POST['franchiseeRep'] : '';
    $ac_id = random_int(100000, 999999);

    // Check for required fields
    if (empty($franchise) || empty($classification) || empty($agreementStart) || empty($agreementDate) || empty($location)) {
        $data['status'] = "error";
        $data['message'] = "Please fill in all required fields.";
    } elseif (!isset($_FILES['notarySealFranchise']) || $_FILES['notarySealFranchise']['error'] != 0) {
        // Check if the file is uploaded
        $data['status'] = "error";
        $data['message'] = "Please upload the Notary Seal for the Franchise.";
    } else {
        // Process the file upload
        $uploadDir = '../assets/images/notarySeals/';
        $originalFileName = $_FILES['notarySealFranchise']['name'];
        $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        $uploadFileName = 'notarySealAgreement-' . date('YmdHis') . '.' . $fileExtension;
        $uploadFile = $uploadDir . $uploadFileName;

        if (move_uploaded_file($_FILES['notarySealFranchise']['tmp_name'], $uploadFile)) {
            // File upload successful, proceed with database insertion
            $insert_query = "INSERT INTO agreement_contract (
                                ac_id,
                                franchisee,
                                classification,
                                rights_granted,
                                franchise_term,
                                agreement_date,
                                location,
                                franchise_fee,
                                ff_note,
                                franchise_package,
                                fp_note,
                                bond,
                                b_note,
                                extra_note,
                                notarization_fr,
                                notarization_fr_rb,
                                notarization_fe,
                                notarization_fe_rb,
                                notary_public_seal,
                                status,
                                datetime_added
                            ) VALUES (
                                '$ac_id',
                                '$franchise',
                                '$classification',
                                '$rightsGranted',
                                '$agreementStart',
                                '$agreementDate',
                                '$location',
                                '$franchiseFee',
                                '$franchiseFeeNote',
                                '$franchisePackage',
                                '$franchisePackageNote',
                                '$bond',
                                '$bondNote',
                                '$extraNoteFranchise',
                                '$franchisor',
                                '$franchisorRep',
                                '$franchisee',
                                '$franchiseeRep',
                                '$uploadFileName',
                                '$docStatus',
                                NOW()
                            )";

            $notif_query = "INSERT INTO notifications(user_id, activity_type, datetime) VALUES ('$ac_id','new_agreement_contract',NOW())";
            $notif_result = mysqli_query($con, $notif_query);

            if (mysqli_query($con, $insert_query) && $notif_result) {
                $data['status'] = "success";
                $data['message'] = "Agreement contract saved successfully";
            } else {
                $data['status'] = "error";
                $data['message'] = "Database error: " . mysqli_error($con);
            }
        } else {
            $data['status'] = "error";
            $data['message'] = "File upload failed.";
        }
    }
} else {
    $data['status'] = "error";
    $data['message'] = "Invalid request method.";
}

echo json_encode($data);
?>