<?php
session_start();
require_once '../php/connection.php';
date_default_timezone_set('Asia/Manila');

$sql = "SELECT 
            q.queue_info_id, 
            q.queue_code, 
            q.patient_id, 
            q.service_name, 
            q.created_at,
            p.firstname, 
            p.lastname,
            p.suffix
        FROM queue_information q
        INNER JOIN patient_records p ON q.patient_id = p.patient_id
        WHERE q.status = 'Waiting' 
        ORDER BY q.created_at ASC";

$res = $conn->query($sql);

echo '<div class="waiting-card-scroll" id="waiting-card-scroll">';

if($res && $res->num_rows > 0){
    while ($row = $res->fetch_assoc()){
        $fullName = ucfirst($row['lastname']) . ', ' . ucfirst($row['firstname']).' '.ucfirst($row['suffix']);
        echo '<div class="patient-row waiting-patient-row">';
        echo '<span class="queue-id">'.htmlspecialchars($row['queue_code']).'</span>';
        echo '<span class="patient-name">'.htmlspecialchars($fullName).'</span>';
        echo '<span class="service-type">'.htmlspecialchars($row['service_name']).'</span>';
        echo '<div class="action-container">';
        echo '<button type="button" class="btn-view-queue" ' .
        'data-id="' .htmlspecialchars($row['queue_info_id']). '" ' .
        'data-code="' .htmlspecialchars($row['queue_code']). '" ' .
        'data-name="' .htmlspecialchars($fullName). '" ' .
        'title="View queue information" aria-label="View queue information for ' .htmlspecialchars($fullName). '">' .
        '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>' .
        '</button>';
        echo '<button type="button" class="btn-delete" ' .
        'data-id="' .htmlspecialchars($row['queue_info_id']). '" ' .
        'data-code="' .htmlspecialchars($row['queue_code']). '" ' .
        'data-name="' .htmlspecialchars($fullName). '"><img src="../svg/delete-icon.svg" alt="check-icon"></button>';
        echo '<button type="button" class="btn-accept" ' .
        'data-id="' .htmlspecialchars($row['queue_info_id']). '" ' .
        'data-code="' .htmlspecialchars($row['queue_code']). '" ' .
        'data-name="' .htmlspecialchars($fullName). '"><img src="../svg/check-icon.svg" alt="check-icon"></button>';
        echo        '</div>';
        echo    '</div>';
    }
} else {
    echo '<div class="empty-state" style="text-align: center; padding: 51px; color: #6b7280; font-size: 14px;">';
    echo '  <p style="margin: 0;">No Waiting patient in records found.</p>';
    echo '</div>';
}
echo '</div>';

?>