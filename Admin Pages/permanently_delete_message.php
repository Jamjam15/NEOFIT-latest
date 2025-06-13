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

// First verify that the message is in trash
$check_stmt = $conn->prepare("SELECT status FROM contact_messages WHERE id = ?");
$check_stmt->bind_param("i", $message_id);
$check_stmt->execute();
$result = $check_stmt->get_result();
$message = $result->fetch_assoc();
$check_stmt->close();

if (!$message || $message['status'] !== 'deleted') {
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode(['success' => false, 'message' => 'Message must be in trash to be permanently deleted']));
}

// Now permanently delete the message
$stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
$stmt->bind_param("i", $message_id);

$response = ['success' => false];

if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Message permanently deleted successfully';
} else {
    $response['message'] = 'Error permanently deleting message: ' . $stmt->error;
}

header('Content-Type: application/json');
echo json_encode($response);

$stmt->close();
$conn->close();
?> 