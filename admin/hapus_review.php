<?php
require '../config/db.php';
$id = $_GET['id'];
$conn->query("DELETE FROM review WHERE id=$id");
header("Location: admin.php");
