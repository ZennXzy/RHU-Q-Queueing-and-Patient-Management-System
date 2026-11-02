<?php
require_once '../api/check-private.php';
authorizeRoles(['Admin']);
require_once '../php/connection.php';
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/account_settings_page.php?msg=" . urlencode("Invalid request method.") . "&type=error");
    exit();
}

// Check if a file was uploaded without errors
if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
    header("Location: ../pages/account_settings_page.php?msg=" . urlencode("Please select a valid SQL backup file to import.") . "&type=error");
    exit();
}

$fileTmpPath = $_FILES['sql_file']['tmp_name'];
$fileName = $_FILES['sql_file']['name'];
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Validate file extension
if ($fileExtension !== 'sql') {
    header("Location: ../pages/account_settings_page.php?msg=" . urlencode("Invalid file format. Only .sql files are allowed.") . "&type=error");
    exit();
}

// Read SQL file contents
$sqlContent = file_get_contents($fileTmpPath);

if (empty($sqlContent)) {
    header("Location: ../pages/account_settings_page.php?msg=" . urlencode("The uploaded SQL file is empty.") . "&type=error");
    exit();
}

// Execute multi-query script (this respects your SET FOREIGN_KEY_CHECKS = 0 inside the file)
if ($conn->multi_query($sqlContent)) {
    do {
        // Clear all results from the buffer so PHP can process every statement completely
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());

    // Insert audit log
    $logStmt = $conn->prepare("INSERT INTO logs (log_message) VALUES (?)");
    if ($logStmt) {
        $logMsg = "Admin imported database backup file: " . $fileName;
        $logStmt->bind_param("s", $logMsg);
        $logStmt->execute();
        $logStmt->close();
    }

    header("Location: ../pages/account_settings_page.php?msg=" . urlencode("Database restored successfully from backup!") . "&type=success");
    exit();
} else {
    $errorMsg = $conn->error;
    header("Location: ../pages/account_settings_page.php?msg=" . urlencode("Database import failed: " . $errorMsg) . "&type=error");
    exit();
}

$conn->close();
?>