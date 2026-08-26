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
          WHERE q.status = 'In Progress' 
          ORDER BY q.created_at ASC";

  $res = $conn->query($sql);

  echo '<div class="in-progress-scroll" id="in-progress-scroll" style="max-height:138px;">';

  if ($res && $res->num_rows > 0) {
      while ($row = $res->fetch_assoc()) {
          $suffix   = !empty($row['suffix']) ? ' ' . ucfirst($row['suffix']) : '';
          $fullName = ucfirst($row['lastname']) . ', ' . ucfirst($row['firstname']) . $suffix;

          echo '<div class="patient-row current-patient-row">';
            echo '  <span class="queue-id">' . htmlspecialchars($row['queue_code']) . '</span>';
            echo '  <span class="patient-name">' . htmlspecialchars($fullName) . '</span>';
            echo '  <span class="service-type">' . htmlspecialchars($row['service_name']) . '</span>';
            echo '  <div class="action-container">';
            echo '    <span class="more-options">⋮</span>';
            echo '    <button type="button" class="btn-complete" ';
            echo '      data-queue-id="' . htmlspecialchars($row['queue_info_id']) . '" ';
            echo '      data-patient-name="' . htmlspecialchars($fullName) . '" ';
            echo '      data-queue-code="' . htmlspecialchars($row['queue_code']) . '" ';
            echo '      data-service="' . htmlspecialchars($row['service_name']) . '" ';
            echo '      data-accepted-at="' . htmlspecialchars($row['created_at']) . '">✔ Complete</button>';
            echo '  </div>';
            echo '</div>';
      }
  } else {
      echo '<div class="empty-state" style="text-align: center; padding: 51px; color: #6b7280; font-size: 14px;">';
      echo '  <p style="margin: 0;">No in-progress records found.</p>';
      echo '</div>';
  }

  echo '</div>';
?>

