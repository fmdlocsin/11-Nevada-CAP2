<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['sales_report'])) {
    $file = $_FILES['sales_report'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'File upload error. Please try again.']);
        exit;
    }

    // Validate file type
    $allowedMimeTypes = [
        'application/vnd.ms-excel', // .xls
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
        'text/csv', // .csv
        'text/plain' // Sometimes .csv files are recognized as text/plain
    ];
    $fileMimeType = mime_content_type($file['tmp_name']);
    if (!in_array($fileMimeType, $allowedMimeTypes)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only Excel and CSV files are allowed.']);
        exit;
    }

    // Save the uploaded file
    $uploadDir = '../../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $filePath = $uploadDir . basename($file['name']);
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        echo json_encode(['status' => 'success', 'message' => 'File uploaded successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save file. Please try again.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
