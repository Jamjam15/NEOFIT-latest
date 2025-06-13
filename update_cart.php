<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in']);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Debug log
error_log('Received data: ' . print_r($data, true));

// Validate cart_id and quantity
if (!isset($data['cart_id']) || !isset($data['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$cart_id = intval($data['cart_id']);
$quantity = intval($data['quantity']);

// Debug log
error_log("Cart ID: $cart_id, Quantity: $quantity");

// Basic validation
if ($cart_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
    exit;
}

if ($quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Quantity must be at least 1']);
    exit;
}

try {
    // Get cart item and check stock
    $check_sql = "SELECT c.*, 
                         CASE c.size 
                             WHEN 'small' THEN p.quantity_small
                             WHEN 'medium' THEN p.quantity_medium
                             WHEN 'large' THEN p.quantity_large
                         END as available_stock
                  FROM cart c
                  JOIN products p ON c.product_id = p.id
                  WHERE c.id = ? AND c.user_id = ?";

    $stmt = $conn->prepare($check_sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ii", $cart_id, $_SESSION['user_id']);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception('Cart item not found');
    }

    $cart_item = $result->fetch_assoc();

    // Check if requested quantity is available
    if ($quantity > $cart_item['available_stock']) {
        throw new Exception('Not enough stock available');
    }

    // Update quantity
    $update_sql = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($update_sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("iii", $quantity, $cart_id, $_SESSION['user_id']);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);

} catch (Exception $e) {
    error_log("Error in update_cart.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}