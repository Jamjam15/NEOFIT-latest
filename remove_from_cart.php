<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in']);
    exit;
}

// Get items from POST data
$items = isset($_POST['items']) ? json_decode($_POST['items'], true) : [];

// Validate input
if (empty($items) || !is_array($items)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Convert items to integers and create placeholders
$cart_ids = array_map('intval', $items);
$placeholders = str_repeat('?,', count($cart_ids) - 1) . '?';

// Delete cart items
$delete_sql = "DELETE FROM cart WHERE id IN ($placeholders) AND user_id = ?";
$stmt = $conn->prepare($delete_sql);

// Create parameter array with cart IDs and user ID
$params = array_merge($cart_ids, [$_SESSION['user_id']]);
$types = str_repeat('i', count($cart_ids)) . 'i';
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Items removed from cart']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error removing items from cart']);
}

$stmt->close();
$conn->close();