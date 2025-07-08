<?php
include __DIR__ . '/../includes/db.php';
$id = $_POST['id'];
$status = $_POST['status'];
mysqli_query($conn, "UPDATE booking SET status='$status' WHERE id=$id");
header('Location: admin.php');
