<?php
$host     = "localhost";
$port     = 3306;
$username = "root";
$password = "";
$database = "lms_db";

$conn = mysqli_connect($host, $username, $password, $database, $port);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
