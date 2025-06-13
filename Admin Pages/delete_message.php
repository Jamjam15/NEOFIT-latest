<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin@1'])) {
    header('HTTP/1.1 403 Forbidden');
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if (!isset($_POST['message_id'])) {
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode(['success' => false, 'message' => 'Message ID is required']));
}

$message_id = (int)$_POST['message_id'];

$stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
$stmt->bind_param("i", $message_id);

$response = ['success' => false];

if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Message deleted successfully';
} else {
    $response['message'] = 'Error deleting message: ' . $stmt->error;
}

header('Content-Type: application/json');
echo json_encode($response);

$stmt->close();
$conn->close();
?> 