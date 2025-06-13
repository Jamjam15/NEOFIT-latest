<?php
session_start();
include '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin@1'])) {
    header('Location: ../index.php');
    exit();
}

// Handle return approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $admin_notes = isset($_POST['admin_notes']) ? trim($_POST['admin_notes']) : '';

    if ($order_id > 0 && ($action === 'approve' || $action === 'reject')) {
        // Start transaction
        mysqli_begin_transaction($conn);

        try {
            $new_status = ($action === 'approve') ? 'Returned' : 'Delivered';
            $return_status = ($action === 'approve') ? 'Approved' : 'Rejected';
            
            // Update order status and return details
            $update_sql = "UPDATE orders SET 
                status = ?,
                return_status = ?,
                return_processed_at = NOW(),
                return_processed_by = ?,
                admin_notes = ?
                WHERE id = ? AND return_status = 'Pending'";
              $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ssisi", $new_status, $return_status, $_SESSION['admin@1'], $admin_notes, $order_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update order status");
            }

            // If no rows were affected, the order might not be in Return Pending status
            if ($stmt->affected_rows === 0) {
                throw new Exception("Order not found or already processed");
            }

            // Commit transaction
            mysqli_commit($conn);
            $_SESSION['success_message'] = "Return request has been " . strtolower($return_status) . " successfully.";
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $_SESSION['error_message'] = $e->getMessage();
        }

        // Redirect to prevent form resubmission
        header('Location: manage_returns.php');
        exit;
    }
}

// Get return requests with order details
$sql = "SELECT o.*, 
        oi.quantity, oi.size, p.product_name, p.product_price, p.photoFront,
        (oi.quantity * p.product_price) as item_total 
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.return_status = 'Pending' OR o.status = 'Return Pending'
        ORDER BY o.return_requested_at DESC";

$result = $conn->query($sql);

if (!$result) {
    // Log the error for debugging
    error_log("Query failed: " . $conn->error);
    die("Error executing query: " . $conn->error);
}

// Debug: Print total number of records found
error_log("Found " . $result->num_rows . " pending return requests");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT Admin - Manage Returns</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .return-request {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .return-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 15px;
        }
        .button-group {
            display: flex;
            gap: 10px;
        }
        .approve-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .approve-btn:hover {
            background: #218838;
        }
        .reject-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .reject-btn:hover {
            background: #c82333;
        }
        .notes-field {
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 80px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .return-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .return-reason {
            font-style: italic;
            color: #6c757d;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <h1>NEOFIT</h1>
            <span class="admin-tag">Admin</span>
        </div>
        <div class="user-icon">
            <i class="fas fa-user-circle"></i>
        </div>
    </header>
    
    <div class="container">
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li onclick="window.location.href='dashboard_page.php'">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </li>
                <li onclick="window.location.href='inbox.php'">
                    <i class="fas fa-inbox"></i>
                    <span>Messages</span>
                </li>
                <li onclick="window.location.href='manage_order_details_page.php'">
                    <i class="fas fa-list"></i>
                    <span>Manage Orders</span>
                </li>
                <li class="active">
                    <i class="fas fa-undo"></i>
                    <span>Returns</span>
                </li>
                <li onclick="window.location.href='customer_orders_page.php'">
                    <i class="fas fa-users"></i>
                    <span>Customer Orders</span>
                </li>
                <li onclick="window.location.href='all_product_page.php'">
                    <i class="fas fa-tshirt"></i>
                    <span>All Products</span>
                </li>
                <li onclick="window.location.href='add_new_product_page.php'">
                    <i class="fas fa-plus-square"></i>
                    <span>Add New Product</span>
                </li>
                <li onclick="window.location.href='payments_page.php'">
                    <i class="fas fa-credit-card"></i>
                    <span>Payments</span>
                </li>
                <li onclick="window.location.href='neocreds_page.php'">
                    <i class="fas fa-coins"></i>
                    <span>NeoCreds</span>
                </li>
                <li onclick="window.location.href='settings.php'">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h2><i class="fas fa-undo"></i> Manage Return Requests</h2>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="return-request">
                        <h3>Return Request #<?php echo str_pad($row['id'], 8, '0', STR_PAD_LEFT); ?></h3>
                        <div class="return-details">
                            <div class="return-header" style="display: grid; grid-template-columns: auto 1fr; gap: 20px; margin-bottom: 20px;">
                                <div class="product-image" style="width: 120px; height: 120px;">
                                    <img src="<?php echo htmlspecialchars($row['photoFront']); ?>" alt="Product" 
                                         style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                                </div>
                                <div class="return-info">
                                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($row['user_name']); ?> (<?php echo htmlspecialchars($row['user_email']); ?>)</p>
                                    <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($row['order_date'])); ?></p>
                                    <p><strong>Return Requested:</strong> <?php echo date('F j, Y', strtotime($row['return_requested_at'])); ?></p>
                                    <p><strong>Product:</strong> <?php echo htmlspecialchars($row['product_name']); ?></p>
                                    <p><strong>Size:</strong> <?php echo htmlspecialchars($row['size']); ?></p>
                                    <p><strong>Quantity:</strong> <?php echo $row['quantity']; ?></p>
                                    <p><strong>Amount:</strong> ₱<?php echo number_format($row['item_total'], 2); ?></p>
                                </div>
                            </div><div class="return-reason">
                                <strong>Return Reason:</strong><br>
                                <?php echo nl2br(htmlspecialchars($row['return_reason'])); ?>
                            </div>
                            <?php if (!empty($row['return_proof_image'])): ?>
                            <div class="return-proof" style="margin-top: 15px;">
                                <strong>Return Proof Image:</strong><br>
                                <div style="margin-top: 10px;">
                                    <img src="../return_proofs/<?php echo htmlspecialchars($row['return_proof_image']); ?>" 
                                         alt="Return Proof" 
                                         style="max-width: 300px; border-radius: 4px; border: 1px solid #ddd;">
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" class="return-actions">
                            <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                            <textarea name="admin_notes" class="notes-field" placeholder="Add notes about this return request (optional)"></textarea>
                            <div class="button-group">
                                <button type="submit" name="action" value="approve" class="approve-btn">
                                    <i class="fas fa-check"></i> Approve Return
                                </button>
                                <button type="submit" name="action" value="reject" class="reject-btn">
                                    <i class="fas fa-times"></i> Reject Return
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="return-request">
                    <p>No pending return requests.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
