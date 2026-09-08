<?php
session_start();
require '../php/connection.php';

$res = $conn->query("SELECT id, username, fullname, email, status, role, created_at, last_active FROM admin_auth");

if($res && $res->num_rows > 0){
    while ($row = $res->fetch_assoc()){
    echo    '<tr>';
    echo    '<td style="text-align: center;">'.htmlspecialchars($row['id']).'</td>';
    echo    '<td>'.htmlspecialchars($row['username']).'</td>';
    echo    '<td>'.htmlspecialchars($row['fullname']).'</td>';
    echo    '<td>'.htmlspecialchars($row['email']).'</td>';
    echo    '<td style="text-align: center;"><span class="badge badge-active">'.htmlspecialchars($row['status']).'</span></td>';
    echo    '<td style="text-align: center;">'.htmlspecialchars($row['role']).'</td>';
    echo    '<td style="text-align: center;">'.htmlspecialchars($row['created_at']).'</td>';
    echo    '<td style="text-align: center;">'.htmlspecialchars($row['last_active']).'</td>';
    echo    '<td>';
    echo    '<div class="table-actions">';
    echo    'P Sample';
    echo    '</div>';
    echo    '</td>';
    echo    '</tr>';
    }
  }

?>