<?php
session_start();
require '../php/connection.php';

$res = $conn->query("SELECT patient_id, firstname, lastname, middlename, suffix, age, gender, contact_number,phil_status, date_registered FROM patient_records ORDER BY firstname ASC");

if($res && $res->num_rows > 0){
  while ($row = $res->fetch_assoc()){
    echo '<tr>';
    echo '<td style="text-align:left;">'.
    htmlspecialchars($row['lastname']).' '.htmlspecialchars($row['firstname']).' '.htmlspecialchars($row['middlename']).' '.htmlspecialchars($row['suffix']).'</td>';
    echo '<td>'.htmlspecialchars($row['age']).'</td>';
    echo '<td>'.htmlspecialchars($row['gender']).'</td>';
    echo '<td>'.htmlspecialchars($row['contact_number']).'</td>';
    echo '<td>'.htmlspecialchars($row['phil_status']).'</td>';
    echo '<td>'.htmlspecialchars($row['date_registered']).'</td>';
    echo '<td><button class="edit-btn" data-id="' . htmlspecialchars($row['patient_id']) . '"><img src="../svg/edit-icon.svg" alt="edit-icon"></button><button class="view-btn" data-id="' . htmlspecialchars($row['patient_id']) . '"><img src="../svg/view-icon.svg" alt="view-icon"></button><button class="delete-btn" data-id="' . htmlspecialchars($row['patient_id']) . '"><img src="../svg/delete-icon.svg" alt="delete-icon"></button></td>';
    echo '</tr>';
  }
} else {
  echo '<tr>';
  echo  '<td colspan="7" class="empty-table-placeholder">';
  echo    '<div class="empty-state">';
  echo      '<p class="empty-title">No Patient Records Found</p>';
  echo      '<p class="search-empty-row">Try adjusting your search criteria or register a new patient.</p>';
  echo    '</div>';
  echo  '</td>';
  echo'</tr>';
}
?>