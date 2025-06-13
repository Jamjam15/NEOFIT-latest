<?php
session_start();
include 'db.php';
include 'includes/address_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    echo "Order ID not provided";
    exit;
}

// Get order details
$sql = "SELECT o.*, oi.quantity, oi.size, p.product_name, p.photoFront, p.product_price as price,
        (oi.quantity * p.product_price) as item_total
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.id = ? AND o.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Order not found or unauthorized access";
    exit;
}

$order = $result->fetch_assoc();

// Get the complete address
$address_data = get_user_address($user_id, $conn);
$complete_address = get_complete_address($address_data);

// Generate QR code data
$qr_data = json_encode([
    'order_id' => $order_id,
    'user_name' => $order['user_name'],
    'user_email' => $order['user_email'],
    'order_date' => $order['order_date'],
    'total_amount' => $order['item_total']
]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt #<?php echo str_pad($order_id, 8, '0', STR_PAD_LEFT); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Alexandria', sans-serif;
        }

        .receipt-wrapper {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            background: white;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .brand-info {
            margin-bottom: 15px;
        }

        .brand-name {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #000;
        }

        .receipt-title {
            font-size: 18px;
            color: #666;
        }

        .order-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .order-number {
            font-size: 18px;
            font-weight: bold;
            color: #000;
        }

        .order-date {
            color: #666;
        }

        .receipt-body {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }

        .info-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #000;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #55a39b;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .info-item {
            padding: 15px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }

        .info-label {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .info-value {
            color: #333;
            font-weight: 500;
            font-size: 16px;
        }

        .order-items {
            margin-top: 30px;
        }

        .item-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 20px;
            align-items: center;
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
        }

        .item-details {
            flex: 1;
        }

        .item-details h4 {
            margin-bottom: 10px;
            color: #333;
            font-size: 16px;
        }

        .item-meta {
            color: #666;
            font-size: 14px;
            display: flex;
            gap: 15px;
        }

        .item-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .item-meta i {
            color: #55a39b;
            font-size: 12px;
        }

        .item-price {
            text-align: right;
            font-weight: bold;
            color: #55a39b;
            font-size: 18px;
            white-space: nowrap;
        }

        .total-section {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            text-align: right;
        }

        .total-row {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 20px;
            margin-bottom: 10px;
        }

        .total-label {
            color: #666;
        }

        .total-value {
            font-weight: bold;
            color: #55a39b;
            font-size: 20px;
        }

        .qr-section {
            margin-top: 40px;
            text-align: center;
        }

        .qr-code {
            background: white;
            padding: 20px;
            border-radius: 8px;
            display: inline-block;
        }

        .qr-code img {
            width: 150px;
            height: 150px;
            margin-bottom: 10px;
        }

        .qr-label {
            font-size: 12px;
            color: #666;
        }

        .action-buttons {
            display: none !important;
        }

        @media (max-width: 768px) {
            .receipt-wrapper {
                padding: 20px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .item-card {
                grid-template-columns: 1fr auto;
                text-align: left;
            }

            .item-image {
                grid-row: span 2;
            }

            .item-details {
                grid-column: 2;
            }

            .item-price {
                grid-column: 2;
                text-align: right;
            }

            .item-meta {
                justify-content: flex-start;
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-wrapper" id="receipt">
        <div class="receipt-header">
            <div class="brand-info">
                <div class="brand-name">NEOFIT</div>
                <div class="receipt-title">Order Receipt</div>
            </div>
            <div class="order-info">
                <div class="order-number">Order #<?php echo str_pad($order_id, 8, '0', STR_PAD_LEFT); ?></div>
                <div class="order-date"><?php echo date('F j, Y', strtotime($order['order_date'])); ?></div>
            </div>
        </div>

        <div class="receipt-body">
            <div class="info-section">
                <div class="section-title">
                    <i class="fas fa-info-circle"></i> Order Information
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Customer Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['user_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Contact Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['contact_number']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['user_email']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Payment Method</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['payment_method']); ?></div>
                    </div>
                </div>
            </div>

            <div class="info-section">
                <div class="section-title">
                    <i class="fas fa-map-marker-alt"></i> Delivery Address
                </div>
                <div class="info-item">
                    <div class="info-value"><?php echo htmlspecialchars($order['delivery_address']); ?></div>
                </div>
            </div>

            <div class="order-items">
                <div class="section-title">
                    <i class="fas fa-box"></i> Order Items
                </div>
                <div class="item-card">
                    <img src="Admin Pages/<?php echo htmlspecialchars($order['photoFront']); ?>" alt="<?php echo htmlspecialchars($order['product_name']); ?>" class="item-image">
                    <div class="item-details">
                        <h4><?php echo htmlspecialchars($order['product_name']); ?></h4>
                        <div class="item-meta">
                            <span><i class="fas fa-tshirt"></i> Size: <?php echo strtoupper(htmlspecialchars($order['size'])); ?></span>
                            <span><i class="fas fa-layer-group"></i> Quantity: <?php echo htmlspecialchars($order['quantity']); ?></span>
                            <span><i class="fas fa-tag"></i> Unit Price: ₱<?php echo number_format($order['price'], 2); ?></span>
                        </div>
                    </div>
                    <div class="item-price">₱<?php echo number_format($order['item_total'], 2); ?></div>
                </div>

                <div class="total-section">
                    <div class="total-row">
                        <span class="total-label">Total Amount:</span>
                        <span class="total-value">₱<?php echo number_format($order['item_total'], 2); ?></span>
                    </div>
                </div>
            </div>

            <div class="qr-section">
                <div class="qr-code">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($qr_data); ?>" alt="Order QR Code">
                    <div class="qr-label">Scan to verify order details</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function closeReceipt() {
            window.parent.closeReceipt();
        }
    </script>
</body>
</html> 