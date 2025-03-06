<?php
include ("database-connection.php");

// Build conditions array for query
$conditions = array();

if (isset($_GET['franchise']) && $_GET['franchise'] != 'all') {
    $franchise = mysqli_real_escape_string($con, $_GET['franchise']);
    $conditions[] = "LOWER(sr.franchisee) = '" . strtolower($franchise) . "'";
}

if (isset($_GET['location']) && $_GET['location'] != 'all' && !empty($_GET['location'])) {
    $location = mysqli_real_escape_string($con, $_GET['location']);
    $conditions[] = "ac.location = '$location'";
}

if (isset($_GET['start']) && !empty($_GET['start']) && isset($_GET['end']) && !empty($_GET['end'])) {
    $start = mysqli_real_escape_string($con, $_GET['start']);
    $end = mysqli_real_escape_string($con, $_GET['end']);
    $conditions[] = "sr.date_added BETWEEN '$start' AND '$end'";
}

// Base query with join
$query = "SELECT sr.*, ac.location 
          FROM sales_report sr
          LEFT JOIN agreement_contract ac ON sr.ac_id = ac.ac_id";

if (count($conditions) > 0) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}
$query .= " ORDER BY sr.date_added DESC, sr.report_id DESC LIMIT 10";

$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $transactions = explode(',', $row['grand_total']);
        // Determine franchise image based on franchise name
        $franchiseLower = strtolower($row['franchisee']);
        $franchise_image = 'default-image.png';
        switch($franchiseLower) {
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
        echo "<tr class='btn-si-data' data-rid='{$row['report_id']}'>";
        echo "<td><img class='franchise-logo' src='../../assets/images/{$franchise_image}' alt='Franchise Image'></td>";
        echo "<td>" . $row['location'] . "</td>";
        echo "<td>" . ucwords($row['services']) . "</td>";
        echo "<td>₱ " . number_format(end($transactions), 2, '.', ',') . "</td>";
        echo "<td>" . $row['date_added'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5'>No records found</td></tr>";
}
?>
