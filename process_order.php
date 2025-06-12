<?php
session_start();
include 'db.php';

ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Function to generate unique transaction ID with microsecond precision
function generateTransactionId() {
    $prefix = 'TXN';
    $time = explode(' ', microtime());
    $timestamp = $time[1] . substr($time[0], 2, 6); // Includes microseconds
    $random = mt_rand(1000, 9999);
    return $prefix . $timestamp . $random;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Log all incoming data
error_log("Processing order with data: " . print_r($_POST, true));
error_log("User session data: " . print_r($_SESSION, true));

// Validate required fields
$required_fields = ['payment_method', 'delivery_address', 'contact_number', 'user_name', 'user_email'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $missing_fields[] = $field;
        error_log("Missing required field: " . $field);
    }
}

if (!empty($missing_fields)) {
    error_log("Missing required fields: " . implode(', ', $missing_fields));
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required fields: ' . implode(', ', $missing_fields)
    ]);
    exit;
}

// Check login with detailed logging
if (!isset($_SESSION['user_id'])) {
    error_log("Checkout failed: User not logged in. Session data: " . print_r($_SESSION, true));
    echo json_encode(['success' => false, 'message' => 'Please log in to place an order']);
    exit;
}

error_log("User authenticated. ID: " . $_SESSION['user_id']);

$user_id = $_SESSION['user_id'];
$user_name = $_POST['user_name'] ?? '';
$user_email = $_POST['user_email'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';
$delivery_address = $_POST['delivery_address'] ?? '';
$contact_number = $_POST['contact_number'] ?? '';
$cart_id = $_POST['cart_id'] ?? null;

if (empty($payment_method) || empty($delivery_address) || empty($contact_number)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}

try {
    error_log("DEBUG: Starting order process...");
    error_log("DEBUG: POST data: " . print_r($_POST, true));
    error_log("DEBUG: Session data: " . print_r($_SESSION, true));

    // Set initial order status using correct ENUM value that matches the database constraint
    $status = 'To Pack'; // Must exactly match ENUM('To Pack','Packed','In Transit','Delivered','Cancelled','Returned')
    error_log("Using order status: " . $status);

    // Validate status before proceeding
    $valid_statuses = ['To Pack', 'Packed', 'In Transit', 'Delivered', 'Cancelled', 'Returned'];
    if (!in_array($status, $valid_statuses)) {
        throw new Exception("Invalid order status: " . $status);
    }

    $conn->begin_transaction();
    error_log("Started transaction");

    // Fetch cart items with error handling
    if ($cart_id) {
        error_log("Processing single cart item ID: " . $cart_id);
        $sql = "SELECT c.*, p.product_name, p.product_price 
                FROM cart c
                JOIN products p ON c.product_id = p.id
                WHERE c.id = ? AND c.user_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparing cart query: " . $conn->error);
        }
        $stmt->bind_param("ii", $cart_id, $user_id);
    } else {
        error_log("Processing all cart items for user");
        $sql = "SELECT c.*, p.product_name, p.product_price 
                FROM cart c
                JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparing cart query: " . $conn->error);
        }
        $stmt->bind_param("i", $user_id);
    }

    if (!$stmt->execute()) {
        throw new Exception("Error fetching cart items: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("No items found in cart");
        throw new Exception("No items found in your cart. Please add items before checking out.");
    }

    error_log("Found " . $result->num_rows . " items in cart");

    $total_amount = 0;
    $cart_items = [];
    while ($item = $result->fetch_assoc()) {
        $subtotal = $item['quantity'] * $item['product_price'];
        $total_amount += $subtotal;
        $cart_items[] = $item;
        error_log("Added item: " . print_r($item, true));
    }

    error_log("Total amount: " . $total_amount);

    // Stock check with additional validation
    foreach ($cart_items as $item) {
        error_log("Checking stock for product ID: " . $item['product_id'] . ", Size: " . $item['size']);
        
        if (!in_array(strtolower($item['size']), ['small', 'medium', 'large'])) {
            throw new Exception("Invalid size '{$item['size']}' for product '{$item['product_name']}'");
        }

        $size_column = 'quantity_' . strtolower($item['size']);
        
        $check_stock_sql = "SELECT $size_column AS stock FROM products WHERE id = ?";
        $check_stock_stmt = $conn->prepare($check_stock_sql);
        if (!$check_stock_stmt) {
            throw new Exception("Error preparing stock check: " . $conn->error);
        }
        
        $check_stock_stmt->bind_param("i", $item['product_id']);
        if (!$check_stock_stmt->execute()) {
            throw new Exception("Error checking stock: " . $check_stock_stmt->error);
        }
        
        $stock_result = $check_stock_stmt->get_result()->fetch_assoc();

        if ($stock_result['stock'] < $item['quantity']) {
            throw new Exception("Not enough stock for '{$item['product_name']}' - Size: {$item['size']}");
        }
    }

    // NeoCreds check with logging
    if ($payment_method === 'NeoCreds') {
        error_log("Checking NeoCreds balance for user: " . $user_id);
        $balance_stmt = $conn->prepare("SELECT neocreds FROM users WHERE id = ?");
        $balance_stmt->bind_param("i", $user_id);
        $balance_stmt->execute();
        $balance_result = $balance_stmt->get_result();
        $user_balance = $balance_result->fetch_assoc()['neocreds'];

        if ($user_balance < $total_amount) {
            echo json_encode(['success' => false, 'message' => 'Insufficient NeoCreds balance']);
            exit;
        }
    }

    // Create the order with standardized status
    $order_sql = "INSERT INTO orders (user_id, user_name, user_email, total_amount, payment_method, delivery_address, contact_number, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $order_stmt = $conn->prepare($order_sql);
    
    if (!$order_stmt) {
        throw new Exception("Error preparing order statement: " . $conn->error);
    }
    
    error_log("Inserting order with status: " . $status);
    $order_stmt->bind_param("issdssss", $user_id, $user_name, $user_email, $total_amount, $payment_method, $delivery_address, $contact_number, $status);
    
    if (!$order_stmt->execute()) {
        error_log("Order insert error: " . $order_stmt->error);
        throw new Exception("Error creating order: " . $order_stmt->error);
    }

    $order_id = $conn->insert_id;

    // Force update the status - using consistent format
    $force_status = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $force_status->bind_param("si", $status, $order_id);
    
    if (!$force_status->execute()) {
        error_log("Status update error: " . $force_status->error);
        throw new Exception("Error updating order status: " . $force_status->error);
    }

    // ✅ Handle NeoCreds payment after order creation
    if ($payment_method === 'NeoCreds') {
        // Handle NeoCreds payment
        $update_balance_sql = "UPDATE users SET neocreds = neocreds - ? WHERE id = ?";
        $update_balance_stmt = $conn->prepare($update_balance_sql);
        $update_balance_stmt->bind_param("di", $total_amount, $user_id);
        
        if (!$update_balance_stmt->execute()) {
            throw new Exception("Error updating NeoCreds balance: " . $conn->error);
        }

        // Record NeoCreds transaction
        $neocreds_sql = "INSERT INTO neocreds_transactions (user_id, user_name, user_email, amount, status, request_date, process_date, is_payment, order_id) 
                        VALUES (?, ?, ?, ?, 'approved', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 1, ?)";
        $neocreds_stmt = $conn->prepare($neocreds_sql);
        $neocreds_stmt->bind_param("issdi", $user_id, $user_name, $user_email, $total_amount, $order_id);
        
        if (!$neocreds_stmt->execute()) {
            throw new Exception("Error recording NeoCreds transaction: " . $conn->error);
        }
    }
    
    // Create payment record
    $transaction_id = generateTransactionId();
    $initial_status = ($payment_method === 'NeoCreds') ? 'success' : 'pending';
    
    $payment_sql = "INSERT INTO payments (transaction_id, order_id, user_name, amount, payment_method, status, payment_date) 
                    VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
    $payment_stmt = $conn->prepare($payment_sql);
    $payment_stmt->bind_param("sisdss", $transaction_id, $order_id, $user_name, $total_amount, $payment_method, $initial_status);
    
    if (!$payment_stmt->execute()) {
        throw new Exception("Error creating payment record: " . $conn->error);
    }

    // ✅ Insert order items and deduct stock per size
    $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, size) VALUES (?, ?, ?, ?, ?)");
    foreach ($cart_items as $item) {
        $item_stmt->bind_param("iiids", $order_id, $item['product_id'], $item['quantity'], $item['product_price'], $item['size']);
        if (!$item_stmt->execute()) {
            throw new Exception("Error adding order items");
        }

        switch (strtolower($item['size'])) {
            case 'small':
                $size_column = 'quantity_small';
                break;
            case 'medium':
                $size_column = 'quantity_medium';
                break;
            case 'large':
                $size_column = 'quantity_large';
                break;
        }

        $update_stock_sql = "UPDATE products SET $size_column = $size_column - ? WHERE id = ?";
        $stock_stmt = $conn->prepare($update_stock_sql);
        $stock_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
        if (!$stock_stmt->execute()) {
            throw new Exception("Error updating stock for '{$item['product_name']}' - Size: {$item['size']}");
        }
    }

    // ✅ Clear cart
    if ($cart_id) {
        $delete_sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("ii", $cart_id, $user_id);
    } else {
        $delete_sql = "DELETE FROM cart WHERE user_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $user_id);
    }

    if (!$delete_stmt->execute()) {
        throw new Exception("Error clearing cart");
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    error_log("Error in checkout process: " . $e->getMessage());
}

// ✅ Handle NeoCreds payment history
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'neocreds_payments') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
        exit;
    }

    try {
        $stmt = $conn->prepare("
            SELECT o.id as order_id, o.total_amount as amount, o.order_date
            FROM orders o
            WHERE o.user_id = ? AND o.payment_method = 'NeoCreds'
            ORDER BY o.order_date DESC
        ");

        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }

        echo json_encode(['status' => 'success', 'transactions' => $transactions]);
    } catch (Exception $e) {
        error_log("Error fetching NeoCreds payments: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch payment history']);
    }
}

$conn->close();
?>