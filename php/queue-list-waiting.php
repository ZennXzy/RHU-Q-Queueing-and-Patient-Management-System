<?php
session_start();
require '../php/connection.php';

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
        echo        '<span class="more-options">⋮</span>';
        echo '<button type="button" class="btn-accept" ' .
        'data-id="' .htmlspecialchars($row['queue_info_id']). '" ' .
        'data-code="' .htmlspecialchars($row['queue_code']). '" ' .
        'data-name="' .htmlspecialchars($fullName). '">Accept</button>';
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