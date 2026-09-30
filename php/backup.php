<?php
require '../api/check-private.php';
authorizeRoles(['Admin']);
require '../php/connection.php';

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

// -------------------------------------------------------------
// 1. SQL FULL BACKUP
// -------------------------------------------------------------
if ($backup_type === 'sql') {
    $filename = "rhu_backup_" . $timestamp . ".sql";
    
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo "-- RHU-Q Database Backup\n";
    echo "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

    foreach ($tables as$table) {
        // Fetch Table Structure
        $createTableQuery =$conn->query("SHOW CREATE TABLE `$table`")->fetch_row();
        echo "-- Table structure for `$table` --\n";
        echo "DROP TABLE IF EXISTS `$table`;\n";
        echo $createTableQuery[1] . ";\n\n";

        // Fetch Table Data
        $result =$conn->query("SELECT * FROM `$table`");
        if ($result->num_rows > 0) {
            echo "-- Data for `$table` --\n";
            while ($row =$result->fetch_assoc()) {
                $keys = array_map(function($k) { return "`$k`"; }, array_keys($row));$values = array_map(function($v) use ($conn) {
                    if ($v === null) return "NULL";
                    return "'" . $conn->real_escape_string($v) . "'";
                }, array_values($row));

                echo "INSERT INTO `$table` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
            }
            echo "\n";
        }
    }
    exit();
}

// -------------------------------------------------------------
// 2. CSV DATA EXPORT (ZIP container if multiple tables)
// -------------------------------------------------------------
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

// Helper function to stream CSV formatted data
function generateCsvStream($conn, $tableName,$resource) {
    $result =$conn->query("SELECT * FROM `$tableName`");
    if ($result->num_rows > 0) {
        $firstRow =$result->fetch_assoc();
        fputcsv($resource, array_keys($firstRow));
        fputcsv($resource, array_values($firstRow));

        while ($row =$result->fetch_assoc()) {
            fputcsv($resource, array_values($row));
        }
    }
}