<?php
$host = "localhost"
$user = ""
$pass = "";
$db = "db_pwd2025"

$conn = myqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}