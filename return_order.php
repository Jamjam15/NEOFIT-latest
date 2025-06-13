<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get form data
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$return_reason = isset($_POST['return_reason']) ? $_POST['return_reason'] : '';
file_put_contents('debug_return.txt', print_r([
    'user_id' => $_SESSION['user_id'],
    'order_id' => $order_id,
    'return_reason' => $return_reason,
    'file_info' => $_FILES['return_proof']
], true), FILE_APPEND);


if ($order_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

// Handle file upload
if (!isset($_FILES['return_proof']) || $_FILES['return_proof']['error'] !== UPLOAD_ERR_OK) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Return proof image is required']);
    exit;
}

$file = $_FILES['return_proof'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

// Validate file size
if ($file['size'] > $maxFileSize) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'File is too large. Maximum size is 5MB']);
    exit;
}

// Validate file type
$allowedTypes = ['image/jpeg', 'image/png'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    file_put_contents('debug_return.txt', "Mime type: $mimeType\n", FILE_APPEND);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG and PNG are allowed']);
    exit;
}

// Create return proofs directory if it doesn't exist
$uploadDir = 'return_proofs/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'return_' . $order_id . '_' . time() . '.' . $extension;
$targetPath = $uploadDir . $filename;

// Verify that the order belongs to the current user and can be returned
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
if ($order['status'] !== 'Delivered') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Cannot return order in current status']);
    exit;
}

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    file_put_contents('debug_return.txt', "Failed to move uploaded file\n", FILE_APPEND);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
    exit;
}

$request_date = date('Y-m-d H:i:s');

if (empty($return_reason)) {
    // Delete the uploaded file if there's no reason
    @unlink($targetPath);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Return reason is required']);
    exit;
}

$update_sql = "UPDATE orders SET 
    return_requested_at = NOW(),
    return_status = 'Pending',
    return_reason = ?,
    return_proof_image = ?,
    status = 'Return Pending'
    WHERE id = ? AND user_id = ? AND status = 'Delivered'";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("ssii", $return_reason, $filename, $order_id, $_SESSION['user_id']);

if (!$update_stmt->execute()) {
    @unlink($targetPath);
    file_put_contents('debug_return.txt', "Update failed: " . $update_stmt->error . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Error updating order status: ' . $update_stmt->error]);
    exit;
} else {
    echo json_encode(['success' => true]);
    exit;
}

?>
