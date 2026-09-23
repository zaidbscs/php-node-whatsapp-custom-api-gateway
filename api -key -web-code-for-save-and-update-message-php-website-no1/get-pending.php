<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Include your database connection
require_once 'db.php';

// Fetch only messages that are currently 'pending'
$result = $conn->query("SELECT id, phone, message FROM whatsapp_queue WHERE status = 'pending' ORDER BY id ASC");
$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'id' => (int)$row['id'],
        'phone' => $row['phone'],
        'message' => $row['message']
    ];
}

echo json_encode([
    'success' => true,
    'count' => count($messages),
    'messages' => $messages
]);

$conn->close();
?>