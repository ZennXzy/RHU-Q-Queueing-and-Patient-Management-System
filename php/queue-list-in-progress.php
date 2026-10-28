<?php
session_start();
require_once '../php/connection.php';
date_default_timezone_set('Asia/Manila');

  $sql = "SELECT 
              q.queue_info_id, 
              q.queue_code, 
              q.patient_id, 
              s.service_name, 
              q.created_at,
              p.firstname, 
              p.lastname,
              p.suffix
          FROM queue_information q
          INNER JOIN patient_records p ON q.patient_id = p.patient_id
          LEFT JOIN services s ON q.service_name = s.service_id
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
            echo '    <button type="button" class="btn-complete" ';
            echo '      data-queue-id="' . htmlspecialchars($row['queue_info_id']) . '" ';
            echo '      data-patient-name="' . htmlspecialchars($fullName) . '" ';
            echo '      data-queue-code="' . htmlspecialchars($row['queue_code']) . '" ';
            echo '      data-service="' . htmlspecialchars($row['service_name']) . '" ';
            echo '      data-accepted-at="' . htmlspecialchars($row['created_at']) . '">Complete</button>';
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

