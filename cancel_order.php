<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get the JSON data from the request
$data = json_decode(file_get_contents('php://input'), true);
$order_id = isset($data['order_id']) ? intval($data['order_id']) : 0;

if ($order_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

// Verify that the order belongs to the current user and can be cancelled
$check_sql = "SELECT status FROM orders WHERE id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Order not found or not authorized']);
    exit;
}

$order = $result->fetch_assoc();
if ($order['status'] === 'Delivered' || $order['status'] === 'Cancelled') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Cannot cancel order in current status']);
    exit;
}

// Update the order status to Cancelled
$update_sql = "UPDATE orders SET status = 'Cancelled' WHERE id = ? AND user_id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("ii", $order_id, $_SESSION['user_id']);

if ($update_stmt->execute()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error updating order status']);
}
?>
