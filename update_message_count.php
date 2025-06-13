<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
    header('HTTP/1.1 403 Forbidden');
    exit(json_encode(['error' => 'Not logged in']));
}

$user_email = $_SESSION['email'];

// Get unread message count
$stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM contact_messages WHERE email = ? AND status = 'unread'");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$unread_count = $result->fetch_assoc()['unread_count'];
$stmt->close();

$conn->close();

header('Content-Type: application/json');
echo json_encode(['unread_count' => $unread_count]);
?> 