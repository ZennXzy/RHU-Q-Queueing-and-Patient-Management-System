<?php
require_once '../api/check-private.php';
authorizeRoles(['Admin']);
require_once '../php/connection.php';
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: account_settings_page_2.php");
    exit();
}

$backup_type =$_POST['backup_type'] ?? 'sql';
$selected_tables =$_POST['tables'] ?? [];

if (empty($selected_tables)) {
    die("Error: No tables selected for backup.");
}

// Allowed table whitelist to prevent SQL injection
$allowed_tables = ['admin_auth', 'appointments', 'logs', 'patient_auth', 'patient_records', 'transactions'];$tables = array_intersect($selected_tables,$allowed_tables);

if (empty($tables)) {
    die("Error: Invalid table selection.");
}

$timestamp = date('Y-m-d_H-i-s');

if ($backup_type === 'sql') {
    $filename = "rhu_backup_" . $timestamp . ".sql";
    
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo "-- RHU-Q Database Backup\n";
    echo "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    
    // 1. Temporarily disable checks to allow smooth cross-table restoration
    echo "SET FOREIGN_KEY_CHECKS = 0;\n";
    echo "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n";

    foreach ($tables as $table) {
        // Fetch Table Structure
        $createTableQuery = $conn->query("SHOW CREATE TABLE `$table`")->fetch_row();
        echo "-- Table structure for `$table` --\n";
        echo "DROP TABLE IF EXISTS `$table`;\n";
        echo $createTableQuery[1] . ";\n\n";

        // Fetch Table Data (Keep primary keys intact for relational integrity)
        $result = $conn->query("SELECT * FROM `$table`");
        if ($result->num_rows > 0) {
            echo "-- Data for `$table` --\n";
            while ($row = $result->fetch_assoc()) {
                $keys = array_map(function($k) { return "`$k`"; }, array_keys($row));
                $values = array_map(function($v) use ($conn) {
                    if ($v === null) return "NULL";
                    return "'" . $conn->real_escape_string($v) . "'";
                }, array_values($row));

                echo "INSERT INTO `$table` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
            }
            echo "\n";
        }
    }

    // 2. Re-enable foreign key checks after all data is imported
    echo "SET FOREIGN_KEY_CHECKS = 1;\n";
    exit();
}

if ($backup_type === 'csv') {
    if (count($tables) === 1) {$table = $tables[0];$filename = "rhu_export_{$table}_" . $timestamp . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        generateCsvStream($conn, $table,$output);
        fclose($output);
        exit();
    } else {
        $zipFilename = "rhu_csv_backup_" . $timestamp . ".zip";
        $zipPath = sys_get_temp_dir() . '/' .$zipFilename;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($tables as $table) {$csvData = fopen('php://temp', 'r+');
                generateCsvStream($conn, $table,$csvData);
                rewind($csvData);$zip->addFromString("{$table}.csv", stream_get_contents($csvData));
                fclose($csvData);
            }
            $zip->close();

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
            header('Content-Length: ' . filesize($zipPath));
            readfile($zipPath);
            unlink($zipPath);
            exit();
        } else {
            die("Error: Could not generate ZIP file.");
        }
    }
}

if ($backup_type === 'zip') {$sqlData = "-- RHU-Q Database Compressed Backup\n";
    $sqlData .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

    foreach ($tables as$table) {
        $createTableQuery =$conn->query("SHOW CREATE TABLE `$table`")->fetch_row();
        $sqlData .= "DROP TABLE IF EXISTS `$table`;\n" . $createTableQuery[1] . ";\n\n";

        $result =$conn->query("SELECT * FROM `$table`");
        while ($row =$result->fetch_assoc()) {
            $keys = array_map(function($k) { return "`$k`"; }, array_keys($row));$values = array_map(function($v) use ($conn) {
                if ($v === null) return "NULL";
                return "'" . $conn->real_escape_string($v) . "'";
            }, array_values($row));$sqlData .= "INSERT INTO `$table` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
        }
        $sqlData .= "\n";
    }

    $zipFilename = "rhu_sql_backup_" . $timestamp . ".zip";
    $zipPath = sys_get_temp_dir() . '/' .$zipFilename;

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $zip->addFromString("rhu_backup_{$timestamp}.sql", $sqlData);$zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);
        unlink($zipPath);
        exit();
    } else {
        die("Error: Failed to construct zip package.");
    }
}
// Helper function to stream CSV formatted data with ID columns excluded and resolved values used
function generateCsvStream($conn, $tableName, $resource) {
    // 1. Determine SQL Query based on table structure
    if ($tableName === 'patient_records') {
        $query = "SELECT pr.*, addr.barangay_address AS resolved_address 
                  FROM `patient_records` pr 
                  LEFT JOIN `barangay` addr ON pr.address = addr.barangay_id";
    }elseif ($tableName === 'transactions') {
        $query = "SELECT t.*, 
                    TRIM(CONCAT(pr.lastname, ', ', pr.firstname, ' ', COALESCE(CONCAT(pr.middlename, ' '), ''), COALESCE(pr.suffix, ''))) AS resolved_patient_name,
                    aa.fullname AS resolved_admin_name
                  FROM `transactions` t
                  LEFT JOIN `patient_records` pr ON t.patient_id = pr.patient_id
                  LEFT JOIN `admin_auth` aa ON t.server_name = aa.id";
    } else {
        $query = "SELECT * FROM `$tableName`";
    }

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $firstRow = $result->fetch_assoc();
        $headers = array_keys($firstRow);

        // Remove temporary address alias header if present
        if (in_array('resolved_address', $headers)) {
            $headers = array_diff($headers, ['resolved_address']);
        }

        // Define columns to exclude per table (dropping ID columns)
        $excludeColumns = [];
        if ($tableName === 'transactions') {
            $excludeColumns = ['service','transaction_id']; 
        } elseif ($tableName === 'patient_records') {
            $excludeColumns = ['patient_id']; 
        }elseif ($tableName === 'logs') {
            $excludeColumns = ['log_id']; 
        }

        // Filter out the excluded columns from headers
        $headers = array_values(array_filter($headers, function($header) use ($excludeColumns) {
            return !in_array($header, $excludeColumns);
        }));

        // Write CSV Header Row
        fputcsv($resource, $headers);

        // Reset result set pointer to the beginning
        $result->data_seek(0);

        while ($row = $result->fetch_assoc()) {
            // Resolve barangay address text for patient_records
            if ($tableName === 'patient_records' && !empty($row['resolved_address'])) {
                $row['address'] = $row['resolved_address'];
            }
            unset($row['resolved_address']);

            // Resolve transaction names independently so one missing relation doesn't block the rest
            if ($tableName === 'transactions') {
                if (!empty($row['resolved_patient_name'])) {
                    $row['patient_id'] = $row['resolved_patient_name'];
                }
                if (!empty($row['resolved_admin_name'])) {
                    $row['server_name'] = $row['resolved_admin_name'];
                }
                // service_snapshot is already a text column in transactions, 
                // so we can use it directly or exclude the ID column 'service'
            }
            unset($row['resolved_patient_name'], $row['resolved_admin_name']);

            // Remove the ID columns from the row dataset
            foreach ($excludeColumns as $col) {
                unset($row[$col]);
            }

            // Write row to CSV stream
            fputcsv($resource, array_values($row));
        }
    }
}