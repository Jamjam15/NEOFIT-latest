<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get the order ID
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

if ($order_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

// Get the current return proof image filename
$sql = "SELECT return_proof_image FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

$row = $result->fetch_assoc();
$filename = $row['return_proof_image'];

// If there's an image, delete it
if (!empty($filename)) {
    $filepath = 'return_proofs/' . $filename;
    if (file_exists($filepath)) {
        unlink($filepath);
    }
}

// Update the database to remove the image reference
$update_sql = "UPDATE orders SET return_proof_image = NULL WHERE id = ? AND user_id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("ii", $order_id, $_SESSION['user_id']);

if ($update_stmt->execute()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to update database']);
}
?>
