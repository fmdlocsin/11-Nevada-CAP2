<?php
session_start();
include("database-connection.php");

// Validate database connection
if (!isset($con) || !$con) {
    die(json_encode(["error" => "Database connection failed: " . mysqli_connect_error()]));
}

// Get filters from the request
$type = $_GET['type'] ?? 'daily';
$franchisees = isset($_GET['franchisees']) ? explode(",", $_GET['franchisees']) : [];
$branches = isset($_GET['branches']) ? explode(",", $_GET['branches']) : [];
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

// Build WHERE clause
$whereClauses = [];

if (!empty($franchisees)) {
    $franchiseeList = "'" . implode("','", array_map(fn($f) => mysqli_real_escape_string($con, $f), $franchisees)) . "'";
    $whereClauses[] = "ac.franchisee IN ($franchiseeList)";
}

if (!empty($branches)) {
    $branchList = "'" . implode("','", array_map(fn($b) => mysqli_real_escape_string($con, $b), $branches)) . "'";
    $whereClauses[] = "ac.location IN ($branchList)";
}

if (!empty($startDate) && !empty($endDate)) {
    $whereClauses[] = "sr.date_added BETWEEN '$startDate' AND '$endDate'";
}

// Define Grouping by Type
switch ($type) {
    case "weekly":
        $groupBy = "YEARWEEK(sr.date_added)";
        $dateField = "DATE_FORMAT(sr.date_added, '%Y-%U') AS date_label";
        break;
    case "monthly":
        $groupBy = "YEAR(sr.date_added), MONTH(sr.date_added)";
        $dateField = "DATE_FORMAT(sr.date_added, '%Y-%m') AS date_label";
        break;
    default:
        $groupBy = "DATE(sr.date_added)";
        $dateField = "DATE(sr.date_added) AS date_label";
        break;
}

// Build SQL Query
$query = "SELECT $dateField, ac.franchisee, ac.location, sr.product_name,
                 SUM(sr.grand_total) AS total_sales,
                 SUM(e.expense_amount) AS total_expenses,
                 (SUM(sr.grand_total) - SUM(e.expense_amount)) AS profit
          FROM sales_report sr
          JOIN agreement_contract ac ON sr.ac_id = ac.ac_id
          LEFT JOIN expenses e ON sr.ac_id = e.location";


if (!empty($whereClauses)) {
    $query .= " WHERE " . implode(" AND ", $whereClauses);
}

$query .= " GROUP BY $groupBy, ac.franchisee, ac.location, sr.product_name ORDER BY date_label DESC";

$result = mysqli_query($con, $query);
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        "date" => $row['date_label'],
        "franchise" => $row['franchisee'],
        "branch" => $row['location'],
        "product_name" => isset($row['product_name']) ? $row['product_name'] : "N/A", // Fix undefined issue
        "total_sales" => number_format($row['total_sales'], 2),
        "total_expenses" => number_format($row['total_expenses'], 2),
        "profit" => number_format($row['profit'], 2)
    ];
    
}

echo json_encode($data);
