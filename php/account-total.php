<?php
session_start();
require '../php/connection.php';

$res = $conn->query("SELECT id FROM admin_auth");
$total = $res ? $res->num_rows : 0;

echo '<span class="stat-value" id="stat-card-total">'.$total.'</span>';

?>