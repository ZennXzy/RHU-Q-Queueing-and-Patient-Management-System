<?php
session_start();
require '../php/connection.php';

$res = $conn->query("SELECT id, username, fullname, email, status, role, created_at, last_active FROM admin_auth");


if($res && $res->num_rows > 0){
    while ($row = $res->fetch_assoc()){
  $statusClass = (strtolower($row['status']) === 'online') ? 'badge-online' : 'badge-offline';
    echo    '<tr>';
    echo    '<td style="text-align: center;">#</td>';
    echo    '<td>'.htmlspecialchars($row['username']).'</td>';
    echo    '<td>'.htmlspecialchars($row['fullname']).'</td>';
    echo    '<td>'.htmlspecialchars($row['email']).'</td>';
    echo '<td style="text-align: center;">';
    echo '<span class="badge ' . $statusClass . '">' . htmlspecialchars(ucfirst($row['status'])) . '</span>';
    echo '</td>';
    echo    '<td style="text-align: center;">'.htmlspecialchars($row['role']).'</td>';
    echo    '<td style="text-align: center;">'.htmlspecialchars($row['created_at']).'</td>';
    echo    '<td style="text-align: center;">'.htmlspecialchars($row['last_active']).'</td>';
    echo    '<td>';
    echo    '<div class="table-actions">';
    echo '<button class="edit-btn" data-id="' . htmlspecialchars($row['id']) . '"><img src="../svg/edit-icon.svg" alt="edit-icon"></button>';
    echo '<button class="delete-btn" data-id="' . htmlspecialchars($row['id']) . '"><img src="../svg/delete-icon.svg" alt="delete-icon"></button>';
    echo '<button class="logout-btn" data-id="' . htmlspecialchars($row['id']) . '"><img src="../svg/logout-icon.svg" alt="logout-icon"></button>';
    echo    '</div>';
    echo    '</td>';
    echo    '</tr>';
    }
  }

?>