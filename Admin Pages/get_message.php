<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin@1'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized');
}

if (!isset($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    exit('Message ID is required');
}

$message_id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id = ?");
$stmt->bind_param("i", $message_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('HTTP/1.1 404 Not Found');
    exit('Message not found');
}

$message = $result->fetch_assoc();

// Update status to 'read' if it's unread
if ($message['status'] === 'unread') {
    $update_stmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
    $update_stmt->bind_param("i", $message_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    // Update the message status in the response
    $message['status'] = 'read';
}

header('Content-Type: application/json');
echo json_encode($message);

$stmt->close();
$conn->close();
?> 