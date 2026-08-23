<?php
session_start();
require '../php/connection.php';

$res = $conn->query("SELECT patient_id FROM patient_records");
$total = $res ? $res->num_rows : 0;
echo '<div class="total-records">';
echo  '<span>Total Registered Patients: <strong id="totalRecordsCount">'.htmlspecialchars($total).'</strong></span>';
echo '</div>';

?>