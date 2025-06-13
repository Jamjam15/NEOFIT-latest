<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get active tab from URL parameter, default to 'all'
$active_tab = isset($_GET['status']) ? strtolower($_GET['status']) : 'all';

// Map old status names to new ones for backward compatibility
$status_map = [
    'pending' => 'to_pack',
    'processing' => 'packed',
    'shipped' => 'in_transit'
];

if (isset($status_map[$active_tab])) {
    $active_tab = $status_map[$active_tab];
}

// Get order counts for each status
$count_sql = "SELECT 
    COUNT(CASE WHEN status = 'To Pack' THEN 1 END) as to_pack_count,
    COUNT(CASE WHEN status = 'Packed' THEN 1 END) as packed_count,
    COUNT(CASE WHEN status = 'In Transit' THEN 1 END) as in_transit_count,
    COUNT(CASE WHEN status = 'Delivered' THEN 1 END) as delivered_count,
    COUNT(CASE WHEN status = 'Cancelled' THEN 1 END) as cancelled_count,
    COUNT(CASE WHEN status = 'Returned' THEN 1 END) as returned_count,
    COUNT(*) as total_count
    FROM orders 
    WHERE user_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$counts = $count_stmt->get_result()->fetch_assoc();

// Get user's orders based on status filter
$sql = "SELECT o.*, oi.quantity, oi.size, p.product_name, p.photoFront, p.product_price as price,
        (oi.quantity * p.product_price) as item_total
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = ?";

// Add status filter if not showing all
if ($active_tab !== 'all') {
    // Convert status format to match database
    $status_display = str_replace('_', ' ', ucwords($active_tab));
    $sql .= " AND o.status = ?";
}
$sql .= " ORDER BY o.order_date DESC";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);

if ($active_tab !== 'all') {
    $stmt->bind_param("is", $user_id, $status_display);
} else {
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT - My Orders</title>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Alexandria', sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 24px;
            font-weight: bold;
        }

        .continue-shopping {
            color: #55a39b;
            text-decoration: none;
        }

        .orders-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .order-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .order-header {
            padding: 15px 20px;
            background-color: #f9f9f9;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-id {
            font-weight: bold;
            color: #000;
        }

        .order-date {
            color: #666;
            font-size: 14px;
        }

        .order-content {
            padding: 20px;
        }

        .order-item {
            display: grid;
            grid-template-columns: 80px 1fr auto;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            align-items: center;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }

        .item-details {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .item-size, .item-quantity {
            color: #666;
            font-size: 14px;
        }

        .item-price {
            color: #55a39b;
            font-weight: bold;
            white-space: nowrap;
            text-align: right;
        }

        .order-footer {
            padding: 15px 20px;
            background-color: #f9f9f9;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-total {
            font-weight: bold;
            font-size: 18px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-to-pack {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-packed {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-in-transit {
            background-color: #d4edda;
            color: #155724;
        }

        .status-delivered {
            background-color: #55a39b;
            color: #fff;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .status-returned {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .status-return-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .shipping-info {
            margin-top: 15px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }

        .shipping-info h4 {
            margin-bottom: 10px;
            color: #666;
        }

        .empty-orders {
            text-align: center;
            padding: 40px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .empty-orders h2 {
            margin-bottom: 10px;
        }

        .empty-orders p {
            color: #666;
            margin-bottom: 20px;
        }

        .shop-now-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #55a39b;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .shop-now-btn:hover {
            background-color: #478c85;
        }

        .order-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            overflow-x: auto;
            padding-bottom: 5px;
        }

        .tab {
            padding: 10px 20px;
            background-color: #f5f5f5;
            border-radius: 20px;
            color: #666;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .tab:hover {
            background-color: #e0e0e0;
            color: #333;
        }

        .tab.active {
            background-color: #55a39b;
            color: white;
        }

        @media (max-width: 768px) {
            .order-tabs {
                padding-bottom: 10px;
            }
            
            .tab {
                padding: 8px 16px;
                font-size: 13px;
            }

            .order-item {
                grid-template-columns: 60px 1fr auto;
                gap: 10px;
            }

            .item-image {
                width: 60px;
                height: 60px;
            }

            .modal-content {
                padding: 20px;
                margin: 10px;
                max-height: 85vh;
            }

            .modal-header h3 {
                font-size: 20px;
            }

            .modal-btn {
                padding: 10px 20px;
                font-size: 14px;
            }
        }

        .order-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .view-receipt-btn {
            padding: 5px 10px;
            background-color: #55a39b;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.3s;
        }

        .view-receipt-btn:hover {
            background-color: #478c85;
        }

        .view-receipt-btn i {
            font-size: 14px;
        }

         .receipt-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

         .receipt-container {
            background: white;
            width: 100%;
            max-width: 800px;
            height: 90vh;
            position: relative;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

          .receipt-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 10px;
            z-index: 1001;
            padding: 10px;
        }

        .receipt-scroll-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

         .close-receipt {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .close-receipt:hover {
            background: #f0f0f0;
            color: #333;
        }
          .download-btn {
            background: #55a39b;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .download-btn:hover {
            background: #478c85;
        }

        body.receipt-open {
            overflow: hidden;
        }


        .return-btn, .cancel-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.3s;
            color: white;
        }

        .return-btn {
            background-color: #ffa500;
        }

        .return-btn:hover {
            background-color: #e69500;
        }

        .cancel-btn {
            background-color: #dc3545;
        }

        .cancel-btn:hover {
            background-color: #c82333;
        }

        .button-disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .modal {            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-content {
            position: relative;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            margin-bottom: 25px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
        }

        .modal-header h3 {
            margin: 0;
            color: #333;
            font-size: 24px;
            font-weight: 600;
        }

        .modal-body {
            margin-bottom: 25px;
        }

        .modal-body p {
            color: #444;
            font-size: 16px;
            margin-bottom: 15px;
        }

        .return-proof-upload {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
            margin: 20px 0;
        }

        .file-input-container {
            margin: 15px 0;
        }

        .file-input {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            cursor: pointer;
        }

        .file-input:hover {
            background-color: #f8f9fa;
        }

        #imagePreview {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-top: 15px;
            text-align: center;
        }

        #previewImage {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }

        .modal-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.2s ease;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .modal-btn-cancel {
            background-color: #6c757d;
            color: white;
        }

        .modal-btn-cancel:hover {
            background-color: #5a6268;
        }

        .modal-btn-confirm {
            background-color: #28a745;
            color: white;
        }

        .modal-btn-confirm:hover {
            background-color: #218838;
        }

        .form-section {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: 500;
            font-size: 16px;
        }

        .form-textarea:focus {
            outline: none;
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }

        .upload-instructions {
            transition: all 0.3s ease;
        }

        .upload-instructions:hover {
            background: #e9ecef;
        }

        .alert-note {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .modal-content {
                padding: 20px;
                margin: 10px;
                max-height: 85vh;
            }

            .modal-header h3 {
                font-size: 20px;
            }

            .modal-btn {
                padding: 10px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

    <div class="container">
        <div class="header">
            <h1 class="page-title">My Orders</h1>
            <a href="landing_page.php" class="continue-shopping">Continue Shopping</a>
        </div>

        <!-- Receipt Overlay Container -->
        <div id="receiptOverlay" class="receipt-overlay" style="display: none;">
            <div class="receipt-container">
                <div class="receipt-actions">
                    <button class="download-btn" onclick="downloadCurrentReceipt()">
                        <i class="fas fa-download"></i> Download
                    </button>
                    <button class="close-receipt" onclick="closeReceipt()">&times;</button>
                </div>
                <div class="receipt-scroll-container">
                    <div id="receiptContent"></div>
                </div>
            </div>
        </div>

        <div class="order-tabs">
            <a href="?status=all" class="tab <?php echo $active_tab === 'all' ? 'active' : ''; ?>">
                All Orders (<?php echo $counts['total_count']; ?>)
            </a>
            <a href="?status=to_pack" class="tab <?php echo $active_tab === 'to_pack' ? 'active' : ''; ?>">
                To Pack (<?php echo $counts['to_pack_count']; ?>)
            </a>
            <a href="?status=packed" class="tab <?php echo $active_tab === 'packed' ? 'active' : ''; ?>">
                Packed (<?php echo $counts['packed_count']; ?>)
            </a>
            <a href="?status=in_transit" class="tab <?php echo $active_tab === 'in_transit' ? 'active' : ''; ?>">
                In Transit (<?php echo $counts['in_transit_count']; ?>)
            </a>
            <a href="?status=delivered" class="tab <?php echo $active_tab === 'delivered' ? 'active' : ''; ?>">
                Delivered (<?php echo $counts['delivered_count']; ?>)
            </a>
            <a href="?status=cancelled" class="tab <?php echo $active_tab === 'cancelled' ? 'active' : ''; ?>">
                Cancelled (<?php echo $counts['cancelled_count']; ?>)
            </a>
            <a href="?status=returned" class="tab <?php echo $active_tab === 'returned' ? 'active' : ''; ?>">
                Returned (<?php echo $counts['returned_count']; ?>)
            </a>
        </div>

        <div class="orders-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($order = $result->fetch_assoc()): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <span class="order-id">Order #<?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?></span>
                            <span class="order-date"><?php echo date('F j, Y', strtotime($order['order_date'])); ?></span>
                        </div>

                        <div class="order-content">
                            <div class="order-item">
                                <img src="Admin Pages/<?php echo htmlspecialchars($order['photoFront']); ?>" alt="<?php echo htmlspecialchars($order['product_name']); ?>" class="item-image">
                                <div class="item-details">
                                    <div class="item-name"><?php echo htmlspecialchars($order['product_name']); ?></div>
                                    <div class="item-size">Size: <?php echo strtoupper(htmlspecialchars($order['size'])); ?></div>
                                    <div class="item-quantity">Quantity: <?php echo htmlspecialchars($order['quantity']); ?></div>
                                </div>
                                <div class="item-price">₱<?php echo number_format($order['price'] * $order['quantity'], 2); ?></div>
                            </div>                            <div class="shipping-info">
                                <h4>Shipping Information</h4>
                                <p><?php echo htmlspecialchars($order['delivery_address']); ?></p>
                                <p>Contact: <?php echo htmlspecialchars($order['contact_number']); ?></p>
                                <p>Payment Method: <?php echo htmlspecialchars($order['payment_method']); ?></p>
                                <?php if ($order['status'] === 'Returned' && !empty($order['admin_notes'])): ?>
                                    <div class="return-notes" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee;">
                                        <h4>Return Notes</h4>
                                        <p style="color: #666;"><?php echo nl2br(htmlspecialchars($order['admin_notes'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="order-footer">
                            <div class="order-total">Total: ₱<?php echo number_format($order['item_total'], 2); ?></div>
                            <div class="order-actions">
                                <div class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </div>                                <button onclick="viewReceipt(<?php echo $order['id']; ?>)" class="view-receipt-btn">
                                    <i class="fas fa-receipt"></i> View Receipt
                                </button>                                <?php if ($order['status'] === 'Delivered'): ?>
                                    <button class="return-btn" onclick="openReturnModal('<?php echo $order['id']; ?>', event)">
                                        <i class="fas fa-undo"></i> Return Order
                                    </button>
                                <?php endif; ?>                                <?php if ($order['status'] === 'To Pack' || $order['status'] === 'Packed'): ?>
                                    <button class="cancel-btn" onclick="openCancelModal(<?php echo $order['id']; ?>, event)">
                                        <i class="fas fa-times"></i> Cancel Order
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-orders">
                    <h2>No orders found for this status</h2>
                    <p>You don't have any orders with the selected status.</p>
                   
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>    <!-- Cancel Order Modal -->
    <div id="cancelModal" class="modal" style="display: none;">
        <div class="modal-content">            <div class="modal-header">
                <h3>Cancel Order</h3>
                <button onclick="closeModal('cancelModal')" class="close-modal" style="position: absolute; right: 15px; top: 15px; background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this order? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-cancel" onclick="closeModal('cancelModal')">No, Keep Order</button>
                <button class="modal-btn modal-btn-confirm" onclick="confirmCancelOrder()">Yes, Cancel Order</button>
            </div>
        </div>
    </div>    <!-- Return Order Modal -->    
    
    <div id="returnModal" class="modal" style="display: none;">
        <div class="modal-content">
            <input type="hidden" id="hiddenOrderId" value="" />
            <div class="modal-header">
                <h3><i class="fas fa-undo"></i> Return Order Request</h3>
                <button onclick="closeModal('returnModal')" class="close-modal" style="position: absolute; right: 15px; top: 15px; background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-section">
                    <label for="returnReason" class="form-label">Reason for Return:</label>
                    <textarea 
                        id="returnReason" 
                        class="form-textarea" 
                        placeholder="Please provide detailed information about why you're returning this item..."
                        style="width: 100%; min-height: 120px; padding: 15px; border: 1px solid #dee2e6; border-radius: 8px; font-size: 15px; resize: vertical; margin-bottom: 20px;"
                    ></textarea>
                </div>
                
                <div class="return-proof-upload">
                    <h4 style="margin-bottom: 15px; color: #333;"><i class="fas fa-camera"></i> Return Proof Photo</h4>
                    <div class="file-input-container">
                        <input 
                            type="file" 
                            id="returnProof" 
                            accept="image/*" 
                            class="file-input"
                        >
                    </div>
                    <div id="imagePreview" style="display: none;">
                        <img id="previewImage">
                        <button 
                            onclick="removeImage()" 
                            class="remove-image-btn" 
                            style="margin-top: 10px; padding: 5px 10px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;"
                        >
                            <i class="fas fa-trash"></i> Remove Image
                        </button>
                    </div>
                    <div class="upload-instructions" style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 6px;">
                        <p style="margin: 0; color: #666; font-size: 14px;">
                            <i class="fas fa-info-circle"></i> <strong>Photo Requirements:</strong>
                        </p>
                        <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #666; font-size: 14px;">
                            <li>Clear, well-lit photo of the item</li>
                            <li>Show any defects or issues clearly</li>
                            <li>File format: JPG or PNG</li>
                            <li>Maximum file size: 5MB</li>
                        </ul>
                    </div>
                </div>

                <div class="alert-note" style="margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                    <i class="fas fa-exclamation-triangle" style="color: #856404;"></i>
                    <span style="color: #856404; margin-left: 8px;">Your return request will be reviewed by our team within 1-2 business days.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-cancel" onclick="closeModal('returnModal')" style="background: #6c757d;">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="modal-btn modal-btn-confirm" onclick="confirmReturnOrder()" style="background: #28a745;">
                    <i class="fas fa-paper-plane"></i> Submit Request
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>        let currentOrderId = null;
        let currentReturnImage = null;

        function viewReceipt(orderId) {
            currentOrderId = orderId;
            const overlay = document.getElementById('receiptOverlay');
            const receiptContent = document.getElementById('receiptContent');
            document.body.classList.add('receipt-open');
            
            // Load receipt content
            fetch('view_order_receipt.php?order_id=' + orderId)
                .then(response => response.text())
                .then(html => {
                    receiptContent.innerHTML = html;
                    overlay.style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error loading receipt:', error);
                    alert('Error loading receipt. Please try again.');
                });
        }

        function closeReceipt() {
            const overlay = document.getElementById('receiptOverlay');
            overlay.style.display = 'none';
            document.body.classList.remove('receipt-open');
            currentOrderId = null;
        }

        async function downloadCurrentReceipt() {
            if (!currentOrderId) return;
            
            try {
                // Get the receipt content element
                const receiptContent = document.getElementById('receiptContent');
                const receiptElement = receiptContent.querySelector('#receipt');
                
                if (!receiptElement) {
                    throw new Error('Receipt element not found');
                }

                // Create a clone of the receipt for capturing
                const clone = receiptElement.cloneNode(true);
                clone.style.position = 'fixed';
                clone.style.left = '-9999px';
                clone.style.top = '0';
                clone.style.width = '800px';
                clone.style.background = 'white';
                clone.style.padding = '20px';
                clone.style.zIndex = '-1000';
                document.body.appendChild(clone);

                // Wait for images to load in the clone
                const images = clone.getElementsByTagName('img');
                await Promise.all([...images].map(img => {
                    if (img.complete) return Promise.resolve();
                    return new Promise(resolve => {
                        img.onload = resolve;
                        img.onerror = resolve;
                    });
                }));

                // Use html2canvas with better quality settings
                const canvas = await html2canvas(clone, {
                    scale: 2,
                    useCORS: true,
                    logging: false,
                    backgroundColor: '#ffffff',
                    width: 800,
                    height: clone.offsetHeight,
                    onclone: (clonedDoc) => {
                        const clonedElement = clonedDoc.querySelector('#receipt');
                        if (clonedElement) {
                            // Remove any action elements from the clone
                            const elementsToRemove = clonedElement.querySelectorAll('.receipt-actions, .close-receipt, .download-btn, .action-buttons, [onclick*="download"], [onclick*="close"]');
                            elementsToRemove.forEach(el => el.remove());
                        }
                    }
                });

                // Convert to blob and download
                canvas.toBlob(function(blob) {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = 'Order-Receipt-' + currentOrderId + '.png';
                    
                    document.body.appendChild(a);
                    a.click();
                    
                    // Cleanup
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    document.body.removeChild(clone);
                }, 'image/png', 1.0);
            } catch (error) {
                console.error('Error downloading receipt:', error);
                alert('Error downloading receipt. Please try again.');
            }
        }

        // Close receipt when clicking outside
        document.addEventListener('click', function(event) {
            const overlay = document.getElementById('receiptOverlay');
            const receiptContainer = document.querySelector('.receipt-container');
            
            if (overlay.style.display === 'flex' && 
                !receiptContainer.contains(event.target) && 
                event.target !== receiptContainer) {
                closeReceipt();
            }
        });

        // Prevent closing when clicking inside receipt
        document.querySelector('.receipt-container').addEventListener('click', function(event) {
            event.stopPropagation();
        });        function openCancelModal(orderId, event) {
            // Prevent event from bubbling up
            event.preventDefault();
            event.stopPropagation();
            
            // Only show the cancel modal if this was triggered by the Cancel Order button
            const sourceElement = event.target.closest('.cancel-btn');
            if (!sourceElement || !sourceElement.classList.contains('cancel-btn')) {
                return; // Exit if not triggered by Cancel Order button
            }

            currentOrderId = orderId;
            const modal = document.getElementById('cancelModal');
            modal.style.display = 'flex';
        }function openReturnModal(orderId, event) {
            // Prevent event from bubbling up
            event.preventDefault();
            event.stopPropagation();
            
            // Only show the return modal if this was triggered by the Return Order button
            const sourceElement = event.target.closest('.return-btn');
            if (!sourceElement || !sourceElement.classList.contains('return-btn')) {
                return; // Exit if not triggered by Return Order button
            }

            currentOrderId = orderId.toString(); // Ensure orderId is a string
            
            // Reset the form
            document.getElementById('returnReason').value = '';
            document.getElementById('returnProof').value = '';
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('previewImage').src = '';
            currentReturnImage = null;
            
            // Add hidden input for order ID
            let hiddenInput = document.getElementById('hiddenOrderId');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.id = 'hiddenOrderId';
                document.querySelector('#returnModal .modal-content').appendChild(hiddenInput);
            }
            hiddenInput.value = currentOrderId;
            
            // Show the modal
            document.getElementById('returnModal').style.display = 'flex';
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'none';
            currentOrderId = null;
        }

        function confirmCancelOrder() {
            if (!currentOrderId) return;
            
            // Perform the cancel order action (e.g., AJAX request)
            fetch('cancel_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ order_id: currentOrderId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close the modal
                    closeModal('cancelModal');
                    // Refresh the page or update the UI accordingly
                    location.reload();
                } else {
                    alert('Error cancelling order: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error cancelling order:', error);
                alert('Error cancelling order. Please try again.');
            });
        }        // Preview return proof image
        document.getElementById('returnProof').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) { // 5MB limit
                    alert('File is too large. Please select an image under 5MB.');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    const previewImage = document.getElementById('previewImage');
                    previewImage.src = e.target.result;
                    preview.style.display = 'block';
                    currentReturnImage = file;
                };
                reader.readAsDataURL(file);
            }
        });        
        
        function confirmReturnOrder() {
            const hiddenOrderId = document.getElementById('hiddenOrderId');
            const orderId = hiddenOrderId ? hiddenOrderId.value : currentOrderId;
            
            console.log('Submitting return for order:', orderId); // Debug line
            
            if (!orderId) {
                alert('Invalid order ID. Please refresh the page and try again.');
                return;
            }
            
            const reason = document.getElementById('returnReason').value.trim();
            const fileInput = document.getElementById('returnProof');
            
            if (!reason) {
                alert('Please provide a reason for returning the order.');
                return;
            }
            
            if (!fileInput.files || !fileInput.files[0]) {
                alert('Please upload a photo of the item you wish to return.');
                return;
            }

            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('return_reason', reason);
            formData.append('return_proof', fileInput.files[0]);
            
            // Show loading state
            const submitButton = document.querySelector('.modal-btn-confirm');
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

            console.log('Submitting FormData:');
            for (let pair of formData.entries()) {
                console.log(pair[0]+ ': ' + pair[1]);
            }

            
            // Perform the return order action with file upload
            fetch('return_order.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close the modal
                    closeModal('returnModal');
                    // Refresh the page or update the UI accordingly
                    location.reload();
                } else {
                    alert('Error returning order: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error returning order:', error);
                alert('Error returning order. Please try again.');
            });
        }        
        
        function removeImage() {
            const fileInput = document.getElementById('returnProof');
            const preview = document.getElementById('imagePreview');
            const previewImage = document.getElementById('previewImage');
            
            // Clear the file input
            fileInput.value = '';
            
            // Hide the preview and clear the image
            preview.style.display = 'none';
            previewImage.src = '';
            
            // Reset the current image variable
            currentReturnImage = null;
            
            // Create a new file input to ensure complete reset
            const newFileInput = document.createElement('input');
            newFileInput.type = 'file';
            newFileInput.id = 'returnProof';
            newFileInput.accept = 'image/*';
            newFileInput.className = 'file-input';
            
            // Replace the old input with the new one
            fileInput.parentNode.replaceChild(newFileInput, fileInput);
            
            // Re-attach the change event listener to the new input
            newFileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.size > 5 * 1024 * 1024) { // 5MB limit
                        alert('File is too large. Please select an image under 5MB.');
                        this.value = '';
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.getElementById('imagePreview');
                        const previewImage = document.getElementById('previewImage');
                        previewImage.src = e.target.result;
                        preview.style.display = 'block';
                        currentReturnImage = file;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    </script>
</body>
</html>