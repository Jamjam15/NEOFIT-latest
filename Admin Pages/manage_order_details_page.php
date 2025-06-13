<?php
include '../db.php';
include '../includes/address_functions.php';

// Get filters from URL
$user_filter = isset($_GET['user']) ? $_GET['user'] : null;
$active_tab = isset($_GET['status']) ? strtolower($_GET['status']) : 'all';
$search_term = isset($_GET['search']) ? $_GET['search'] : null;

// Map old status names to new ones for backward compatibility
$status_map = [
    'pending' => 'To Pack',
    'processing' => 'Packed',
    'shipped' => 'In Transit'
];

if (isset($status_map[$active_tab])) {
    $active_tab = $status_map[$active_tab];
}

// Define valid status transitions with consistent Title Case format
$valid_status_transitions = [
    'To Pack' => ['Packed', 'Cancelled'],
    'Packed' => ['In Transit', 'Cancelled'],
    'In Transit' => ['Delivered'],
    'Delivered' => ['Returned'],
    'Cancelled' => [],
    'Returned' => []
];
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : null;
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : null;

// Base query
$sql = "SELECT o.*, 
               p.product_name as product_display_name,
               p.photoFront as product_image,
               oi.quantity,
               oi.product_id,
               oi.size,
               p.product_price as price,
               (oi.quantity * p.product_price) as item_total,
               o.user_id
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE 1=1";
$params = [];
$types = "";

// Add filters
if ($user_filter) {
    $sql .= " AND o.user_email = ?";
    $params[] = $user_filter;
    $types .= "s";
}

if ($active_tab !== 'all') {
    $sql .= " AND LOWER(o.status) = LOWER(?)";
    $params[] = str_replace('_', ' ', $active_tab); // Convert status format for DB
    $types .= "s";
}

if ($search_term) {
    $search_term = "%$search_term%";
    $sql .= " AND (o.user_name LIKE ? OR o.user_email LIKE ? OR CAST(o.id AS CHAR) LIKE ? OR p.product_name LIKE ?)";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
    $types .= "ssss";
}

if ($date_from) {
    $sql .= " AND DATE(o.order_date) >= ?";
    $params[] = $date_from;
    $types .= "s";
}

if ($date_to) {
    $sql .= " AND DATE(o.order_date) <= ?";
    $params[] = $date_to;
    $types .= "s";
}

// Add sorting
$sql .= " ORDER BY o.order_date DESC";

// Prepare and execute query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Format the results to include complete address
$orders = [];
while ($row = $result->fetch_assoc()) {
    $address_data = get_user_address($row['user_id'], $conn);
    $row['delivery_address'] = get_complete_address($address_data);
    $orders[] = $row;
}

// Get order statistics
$stats_sql = "SELECT 
    COUNT(DISTINCT o.id) as total_orders,
    COUNT(DISTINCT o.user_id) as unique_customers,
    COALESCE(SUM(oi.quantity * p.product_price), 0) as total_revenue,
    COUNT(DISTINCT CASE WHEN o.status = 'To Pack' THEN o.id END) as to_pack_orders,
    COUNT(DISTINCT CASE WHEN o.status = 'Packed' THEN o.id END) as packed_orders,
    COUNT(DISTINCT CASE WHEN o.status = 'In Transit' THEN o.id END) as in_transit_orders,
    COUNT(DISTINCT CASE WHEN o.status = 'Delivered' THEN o.id END) as delivered_orders,
    COUNT(DISTINCT CASE WHEN o.status = 'Cancelled' THEN o.id END) as cancelled_orders,
    COUNT(DISTINCT CASE WHEN o.status = 'Returned' THEN o.id END) as returned_orders
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
LEFT JOIN products p ON oi.product_id = p.id";
$stats_result = $conn->query($stats_sql)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT Admin - Order Management</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .filters-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .filter-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-label {
            font-size: 0.9em;
            color: #666;
        }

        .filter-input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9em;
        }

        /* Filter status dropdown styling to match group */
        .filter-input[name="status"] {
            padding: 8px 32px 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9em;
            background: white url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="6"><path d="M0 0h12L6 6z" fill="%23666"/></svg>') no-repeat;
            background-position: right 12px center;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 160px;
        }

        .filter-input[name="status"]:focus {
            outline: none;
            border-color: #7ab55c;
            box-shadow: 0 0 0 2px rgba(122, 181, 92, 0.1);
        }

        .filter-input[name="status"]:hover {
            border-color: #7ab55c;
        }

        .filter-input[name="status"] option {
            padding: 8px;
            background: white;
            color: #333;
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
        }

        .filter-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.2s;
        }

        .apply-btn {
            background: #7ab55c;
            color: white;
        }

        .reset-btn {
            background: #6c757d;
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-value {
            font-size: 1.5em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9em;
            color: #666;
        }

        .order-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .order-id {
            font-size: 1.1em;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .order-date {
            color: #666;
            font-size: 0.9em;
            font-weight: normal;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .order-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .product-info, .customer-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }

        .product-info {
            display: flex;
            gap: 20px;
        }

        .product-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
        }

        .product-details {
            flex: 1;
        }

        .product-details h4 {
            margin-bottom: 15px;
            color: #333;
        }

        .product-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }

        .product-meta span {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #555;
        }

        .product-meta i {
            color: #7ab55c;
            width: 16px;
        }

        .customer-info h4 {
            margin-bottom: 15px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .customer-info i {
            color: #7ab55c;
        }

        .customer-details {
            display: grid;
            gap: 10px;
        }

        .customer-details p {
            display: flex;
            gap: 8px;
            align-items: baseline;
        }

        .customer-details strong {
            min-width: 80px;
            color: #666;
        }

        .order-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn i {
            font-size: 1em;
        }

        .btn-view {
            background: #6c757d;
            color: white;
        }

        .btn-status {
            background: #7ab55c;
            color: white;
        }

        .btn-return {
            background: #ffc107;
            color: #000;
        }

        .btn-cancel {
            background: #dc3545;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
        }

        .status-badge.status-to-pack {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-badge.status-packed {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-badge.status-in-transit {
            background-color: #d4edda;
            color: #155724;
        }

        .status-badge.status-delivered {
            background-color: #c3e6cb;
            color: #1e7e34;
        }

        .status-badge.status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-badge.status-returned {
            background-color: #e2e3e5;
            color: #383d41;
        }

        @media (max-width: 768px) {
            .order-details {
                grid-template-columns: 1fr;
            }

            .product-info {
                flex-direction: column;
            }

            .product-image {
                width: 100%;
                height: 200px;
            }

            .product-meta {
                grid-template-columns: 1fr;
            }

            .order-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        .status-section {
            margin-bottom: 40px;
        }

        .status-title {
            font-size: 1.5em;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }

        .status-section:empty {
            display: none;
        }

        .order-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            overflow-x: auto;
            padding-bottom: 5px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
            background-color: #7ab55c;
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
                <li class="active">

                    <i class="fas fa-list"></i>
                    <span>Manage Orders</span>
                </li>
                <li onclick="window.location.href='manage_returns.php'">
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
            <?php if ($user_filter): ?>
                <a href="customer_orders_page.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to All Customers
                </a>
            <?php endif; ?>

            <h1 class="page-title">
                <?php 
                if ($user_filter) {
                    $user_name = $result->num_rows > 0 ? $result->fetch_array()['user_name'] : 'Unknown User';
                    $result->data_seek(0); // Reset result pointer
                    echo "Orders for " . htmlspecialchars($user_name);
                } else {
                    echo "Order Management";
                }
                ?>
            </h1>

            <!-- Order Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats_result['total_orders']; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats_result['unique_customers']; ?></div>
                    <div class="stat-label">Unique Customers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">₱<?php echo number_format($stats_result['total_revenue'], 2); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats_result['to_pack_orders']; ?></div>
                    <div class="stat-label">To Pack</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats_result['packed_orders']; ?></div>
                    <div class="stat-label">Packed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats_result['in_transit_orders']; ?></div>
                    <div class="stat-label">In Transit</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats_result['delivered_orders']; ?></div>
                    <div class="stat-label">Delivered</div>
                </div>
            </div>

            <!-- Order Tabs -->
            <div class="order-tabs">
                <a href="?status=all<?php echo $user_filter ? '&user=' . urlencode($user_filter) : ''; ?>" 
                   class="tab <?php echo $active_tab === 'all' ? 'active' : ''; ?>">
                    All Orders (<?php echo $stats_result['total_orders']; ?>)
                </a>
                <a href="?status=to_pack<?php echo $user_filter ? '&user=' . urlencode($user_filter) : ''; ?>" 
                   class="tab <?php echo $active_tab === 'to_pack' ? 'active' : ''; ?>">
                    To Pack (<?php echo $stats_result['to_pack_orders']; ?>)
                </a>
                <a href="?status=packed<?php echo $user_filter ? '&user=' . urlencode($user_filter) : ''; ?>" 
                   class="tab <?php echo $active_tab === 'packed' ? 'active' : ''; ?>">
                    Packed (<?php echo $stats_result['packed_orders']; ?>)
                </a>
                <a href="?status=in_transit<?php echo $user_filter ? '&user=' . urlencode($user_filter) : ''; ?>" 
                   class="tab <?php echo $active_tab === 'in_transit' ? 'active' : ''; ?>">
                    In Transit (<?php echo $stats_result['in_transit_orders']; ?>)
                </a>
                <a href="?status=delivered<?php echo $user_filter ? '&user=' . urlencode($user_filter) : ''; ?>" 
                   class="tab <?php echo $active_tab === 'delivered' ? 'active' : ''; ?>">
                    Delivered (<?php echo $stats_result['delivered_orders']; ?>)
                </a>
                <a href="?status=cancelled<?php echo $user_filter ? '&user=' . urlencode($user_filter) : ''; ?>" 
                   class="tab <?php echo $active_tab === 'cancelled' ? 'active' : ''; ?>">
                    Cancelled (<?php echo $stats_result['cancelled_orders']; ?>)
                </a>
                <a href="?status=returned<?php echo $user_filter ? '&user=' . urlencode($user_filter) : ''; ?>" 
                   class="tab <?php echo $active_tab === 'returned' ? 'active' : ''; ?>">
                    Returned (<?php echo $stats_result['returned_orders']; ?>)
                </a>
            </div>

            <!-- Filters Section -->
            <div class="filters-section">
                <form id="filterForm" method="GET">
                    <div class="filters-grid">
                        <div class="filter-item">
                            <label class="filter-label">Search</label>
                            <input type="text" name="search" class="filter-input" 
                                   placeholder="Order ID, Customer, or Product" 
                                   value="<?php echo htmlspecialchars(str_replace('%', '', $search_term ?? '')); ?>">
                        </div>
                        <div class="filter-item">
                            <label class="filter-label">Date From</label>
                            <input type="date" name="date_from" class="filter-input" 
                                   value="<?php echo htmlspecialchars($date_from ?? ''); ?>">
                        </div>
                        <div class="filter-item">
                            <label class="filter-label">Date To</label>
                            <input type="date" name="date_to" class="filter-input" 
                                   value="<?php echo htmlspecialchars($date_to ?? ''); ?>">
                        </div>
                    </div>
                    <div class="filter-buttons">
                        <button type="submit" class="filter-btn apply-btn">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <button type="button" class="filter-btn reset-btn" onclick="resetFilters()">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Orders List -->
            <?php if (empty($orders)): ?>
                <div class="no-orders">
                    <i class="fas fa-box-open"></i>
                    <p>No orders found</p>
                </div>
            <?php else: ?>
                <!-- Debug output -->
                <div style="display: none;">
                    <h3>Debug Information:</h3>
                    <pre><?php print_r($orders); ?></pre>
                </div>

                <?php
                // Group orders by status
                $grouped_orders = [
                    'To Pack' => [],
                    'Packed' => [],
                    'In Transit' => [],
                    'Delivered' => [],
                    'Cancelled' => [],
                    'Returned' => []
                ];
                
                foreach ($orders as $order) {
                    $grouped_orders[$order['status']][] = $order;
                }
                
                // Display orders grouped by status
                foreach ($grouped_orders as $status => $status_orders):
                    if (!empty($status_orders)):
                ?>
                    <div class="status-section">
                        <h2 class="status-title"><?php echo $status; ?> Orders</h2>
                        <?php foreach ($status_orders as $row): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-id">
                                        Order #<?php echo str_pad($row['id'], 8, '0', STR_PAD_LEFT); ?>
                                        <span class="order-date">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('F d, Y', strtotime($row['order_date'])); ?>
                                        </span>
                                    </div>
                                    <div class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                        <!-- Debug: Raw status value -->
                                        <span style="display: none;">(Raw: <?php echo htmlspecialchars($row['status']); ?>)</span>
                                    </div>
                                </div>
                                <div class="order-details">
                                    <div class="product-info">
                                        <img src="<?php echo $row['product_image']; ?>" alt="Product" class="product-image">
                                        <div class="product-details">
                                            <h4><?php echo htmlspecialchars($row['product_display_name']); ?></h4>
                                            <div class="product-meta">
                                                <span><i class="fas fa-box"></i> Size: <?php echo strtoupper($row['size'] ?? 'N/A'); ?></span>
                                                <span><i class="fas fa-layer-group"></i> Quantity: <?php echo $row['quantity']; ?></span>
                                                <span><i class="fas fa-tag"></i> Unit Price: ₱<?php echo number_format($row['price'], 2); ?></span>
                                                <span><i class="fas fa-money-bill"></i> Total: ₱<?php echo number_format($row['item_total'], 2); ?></span>
                                                <span><i class="fas fa-credit-card"></i> Payment: <?php echo htmlspecialchars($row['payment_method'] ?? 'N/A'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="customer-info">
                                        <h4><i class="fas fa-user"></i> Customer Details</h4>
                                        <div class="customer-details">
                                            <p><strong>Name:</strong> <?php echo htmlspecialchars($row['user_name']); ?></p>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($row['user_email']); ?></p>
                                            <p><strong>Contact:</strong> <?php echo htmlspecialchars($row['contact_number']); ?></p>
                                            <p><strong>Address:</strong> <?php echo htmlspecialchars($row['delivery_address']); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="order-actions">
                                    <a href="view_order.php?id=<?php echo $row['id']; ?>" class="btn btn-view">
                                        <i class="fas fa-eye"></i> View Full Details
                                    </a>
                                    <?php
                                    // Get current status in lowercase and convert spaces to underscores
                                    $current_status = strtolower($row['status']);
                                    
                                    // Show appropriate action button based on current status
                                    switch($current_status) {
                                        case 'to pack':
                                            ?>
                                            <button type="button" class="btn btn-status" onclick="updateOrderStatus('<?php echo $row['id']; ?>', 'packed')">
                                                <i class="fas fa-box"></i> Move to Packed
                                            </button>
                                            <button type="button" class="btn btn-cancel" onclick="updateOrderStatus('<?php echo $row['id']; ?>', 'cancelled')">
                                                <i class="fas fa-times-circle"></i> Cancel Order
                                            </button>
                                            <?php
                                            break;
                                            
                                        case 'packed':
                                            ?>
                                            <button type="button" class="btn btn-status" onclick="updateOrderStatus('<?php echo $row['id']; ?>', 'in_transit')">
                                                <i class="fas fa-shipping-fast"></i> Move to In Transit
                                            </button>
                                            <button type="button" class="btn btn-cancel" onclick="updateOrderStatus('<?php echo $row['id']; ?>', 'cancelled')">
                                                <i class="fas fa-times-circle"></i> Cancel Order
                                            </button>
                                            <?php
                                            break;
                                            
                                        case 'in transit':
                                            ?>
                                            <button type="button" class="btn btn-status" onclick="updateOrderStatus('<?php echo $row['id']; ?>', 'delivered')">
                                                <i class="fas fa-check-circle"></i> Move to Delivered
                                            </button>
                                            <?php
                                            break;
                                            
                                        case 'delivered':
                                            ?>
                                            <button type="button" class="btn btn-return" onclick="updateOrderStatus('<?php echo $row['id']; ?>', 'returned')">
                                                <i class="fas fa-undo"></i> Mark as Returned
                                            </button>
                                            <?php
                                            break;
                                            
                                        case 'cancelled':
                                            // No actions available for cancelled orders
                                            break;
                                            
                                        case 'returned':
                                            // No actions available for returned orders
                                            break;
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php 
                    endif;
                endforeach; 
                ?>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Status transitions using Title Case with spaces to match database format
        const statusTransitions = {
            'To Pack': ['Packed', 'Cancelled'],
            'Packed': ['In Transit', 'Cancelled'],
            'In Transit': ['Delivered'],
            'Delivered': ['Returned'],
            'Cancelled': [],
            'Returned': []
        };

        function updateOrderStatus(orderId, newStatus) {
            // Get current status from the order's status badge
            const orderCard = event.target.closest('.order-card');
            const currentStatusBadge = orderCard.querySelector('.status-badge');
            // Get the raw status text without any hidden content
            const currentStatus = currentStatusBadge.childNodes[0].textContent.trim();

            // Convert underscored status to exact ENUM format
            const statusMap = {
                'to_pack': 'To Pack',
                'packed': 'Packed',
                'in_transit': 'In Transit',
                'delivered': 'Delivered',
                'cancelled': 'Cancelled',
                'returned': 'Returned'
            };
            const displayStatus = statusMap[newStatus.toLowerCase()] || newStatus;

            // Debug output
            console.log('Current Status:', currentStatus);
            console.log('New Status:', displayStatus);
            console.log('Valid Transitions:', statusTransitions[currentStatus]);

            // Validate the transition
            if (!statusTransitions[currentStatus]?.includes(displayStatus)) {
                alert(`Invalid status transition from "${currentStatus}" to "${displayStatus}"`);
                return;
            }
            
            if (!confirm(`Are you sure you want to update this order to ${displayStatus}?`)) {
                return;
            }

            // Show loading state
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

            // Create form data
            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('new_status', displayStatus);

            // Send AJAX request
            fetch('update_order_status.php', {
                method: 'POST',
                body: formData
            })
            .then(async response => {
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    // If response is not JSON, get the text and throw it
                    const text = await response.text();
                    throw new Error('Invalid response format. Server said: ' + text);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to update order status');
                    button.disabled = false;
                    button.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating status: ' + error.message);
                button.disabled = false;
                button.innerHTML = originalText;
            });
        }

        function resetFilters() {
            const currentStatus = new URLSearchParams(window.location.search).get('status') || 'all';
            document.querySelectorAll('.filter-input').forEach(input => {
                if (input.type !== 'hidden') {
                    input.value = '';
                }
            });
            window.location.href = `?status=${currentStatus}`;
        }
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>