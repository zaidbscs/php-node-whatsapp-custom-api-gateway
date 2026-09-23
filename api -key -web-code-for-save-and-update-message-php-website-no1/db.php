<?php
$host = 'localhost';
$username = 'database_username';
$password = 'database_password';
$database = 'database_name';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}
?>