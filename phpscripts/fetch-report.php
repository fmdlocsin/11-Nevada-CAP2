<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json'); // Ensure JSON response

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
        $groupBy = "YEARWEEK(sr.date_added), ac.franchisee, ac.location, sr.product_name";
        $dateField = "CONCAT(DATE_FORMAT(DATE_SUB(sr.date_added, INTERVAL WEEKDAY(sr.date_added) DAY), '%M %e, %Y'), ' - ', 
                  DATE_FORMAT(DATE_ADD(DATE_SUB(sr.date_added, INTERVAL WEEKDAY(sr.date_added) DAY), INTERVAL 6 DAY), '%M %e, %Y')) 
                  AS date_label";
        break;    
    case "monthly":
        $groupBy = "YEAR(sr.date_added), MONTH(sr.date_added), ac.franchisee, ac.location, sr.product_name";
        $dateField = "DATE_FORMAT(sr.date_added, '%M %Y') AS date_label"; // Shows full month name
        break;
    default:
        $groupBy = "DATE(sr.date_added), ac.franchisee, ac.location, sr.product_name";
        $dateField = "DATE(sr.date_added) AS date_label";
        break;
}

// Build SQL Query
$query = "SELECT 
            $dateField, 
            ac.franchisee, 
            ac.location, 
            sr.product_name,
            SUM(sr.grand_total) AS total_sales,
            COALESCE(expenses_table.total_expenses, 0) AS total_expenses, -- ✅ Fetch expenses the same way as KPI
            (SUM(sr.grand_total) - COALESCE(expenses_table.total_expenses, 0)) AS profit
          FROM sales_report sr
          JOIN agreement_contract ac ON sr.ac_id = ac.ac_id
          LEFT JOIN (
            SELECT e.location, SUM(e.expense_amount) AS total_expenses
            FROM expenses e
            JOIN agreement_contract ac ON e.location = ac.ac_id  -- ✅ Match KPI's way of fetching expenses
            WHERE e.date_added BETWEEN '$startDate' AND '$endDate'
            GROUP BY e.location
        ) expenses_table ON ac.ac_id = expenses_table.location";

// **Add WHERE Clause if Conditions Exist**
if (!empty($whereClauses)) {
    $query .= " WHERE " . implode(" AND ", $whereClauses);
}

// **Ensure GROUP BY is Properly Appended**
if (!empty($groupBy)) {
    $query .= " GROUP BY $groupBy";
} else {
    die(json_encode(["error" => "Invalid GROUP BY clause"]));
}

// **Add ORDER BY**
$query .= " ORDER BY date_label DESC, ac.franchisee, ac.location, sr.product_name";




$result = mysqli_query($con, $query);
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        "date" => $row['date_label'],
        "franchise" => $row['franchisee'],
        "branch" => $row['location'],
        "product_name" => isset($row['product_name']) && !empty($row['product_name']) ? $row['product_name'] : "N/A",
        "total_sales" => number_format($row['total_sales'], 2),
        "total_expenses" => number_format($row['total_expenses'], 2),
        "profit" => number_format($row['profit'], 2)
    ];
}

echo json_encode($data);

error_log("Generated SQL Query: " . $query);
$result = mysqli_query($con, $query);

if (!$result) {
    die(json_encode(["error" => "SQL Error: " . mysqli_error($con)]));  // ✅ Captures the actual SQL error
}



