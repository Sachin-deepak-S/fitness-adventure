<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username   = "root";
$password   = "1234";   // your MySQL root password
$dbname     = "fitness_db";
$port       = 3307;     // since your my.cnf shows port=3307

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
} else {
    echo "✅ Connected successfully to fitness_db!";
}
?>
