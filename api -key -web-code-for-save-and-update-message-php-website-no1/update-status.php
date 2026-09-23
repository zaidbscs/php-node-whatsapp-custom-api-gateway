<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include the database connection
require_once 'db.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? '';
$status = $input['status'] ?? 'sent'; // e.g., 'sent' or 'failed'

if (empty($id)) {
    echo json_encode(['success' => false, 'error' => 'Message ID is required']);
    exit();
}

$stmt = $conn->prepare("UPDATE whatsapp_queue SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Message status updated successfully!']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update status']);
}

$stmt->close();
$conn->close();
?>